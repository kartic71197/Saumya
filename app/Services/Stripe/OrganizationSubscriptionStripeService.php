<?php

namespace App\Services\Stripe;

use App\Models\Organization;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Stripe\Invoice;
use Laravel\Cashier\Subscription as CashierSubscription;

class OrganizationSubscriptionStripeService
{
    protected OrganizationStripeService $orgService;
    protected SubscriptionStripeService $planService;

    public function __construct(
        OrganizationStripeService $orgService,
        SubscriptionStripeService $planService
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->orgService = $orgService;
        $this->planService = $planService;
    }

    /**
     * Create Stripe subscription + local mirror
     */
    public function createSubscription(Organization $org, Plan $plan): ?CashierSubscription
    {
        try {
            /** 1️⃣ Ensure Stripe customer */
            if (!$org->stripe_id) {
                $this->orgService->syncOrganization($org);
                $org->refresh();
            }

            /** CLOSE existing subscriptions */
            $this->closeExistingSubscriptions($org);

            /** 2️⃣ Ensure plan exists on Stripe */
            if (!$plan->stripe_product_id || !$plan->stripe_price_id) {
                $this->planService->sync($plan);
                $plan->refresh();
            }

            $trialEnd = $this->calculateTrialEndTimestamp();

            /** 3️⃣ Create subscription on STRIPE */
            $stripeSubscription = StripeSubscription::create([
                'customer' => $org->stripe_id,
                'items' => [
                    [
                        'price' => $plan->stripe_price_id,
                    ]
                ],
                'trial_end' => $trialEnd,
                'payment_behavior' => 'default_incomplete',
                'collection_method' => 'send_invoice',
                'days_until_due' => 7,
                'metadata' => [
                    'organization_id' => $org->id,
                    'plan_id' => $plan->id,
                ],
            ]);

            /** 4️⃣ Finalize invoice → email sent immediately */
            // if ($stripeSubscription->latest_invoice) {
            //     $invoice = Invoice::retrieve($stripeSubscription->latest_invoice);
            //     $invoice->finalize();
            // }


            /** 5️⃣ Create LOCAL Cashier subscription (mirror only) */
            $localSubscription = CashierSubscription::create([
                'organization_id' => $org->id, // or organization_id if customized
                'name' => 'default',
                'stripe_id' => $stripeSubscription->id,
                'stripe_status' => $stripeSubscription->status,
                'stripe_price' => $plan->stripe_price_id,
                'quantity' => 1,
                'trial_ends_at' => Carbon::createFromTimestamp($trialEnd),
            ]);

            /** 6️⃣ Update org plan */
            $org->update(['plan_id' => $plan->id]);

            Log::info('Subscription created successfully', [
                'org_id' => $org->id,
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);

            return $localSubscription;

        } catch (\Exception $e) {
            Log::error('Failed to create subscription', [
                'org_id' => $org->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function closeExistingSubscriptions(Organization $org): void
    {
        if (!$org->stripe_id) {
            return;
        }

        $subscriptions = StripeSubscription::all([
            'customer' => $org->stripe_id,
            'status' => 'all',
            'limit' => 10,
        ]);

        foreach ($subscriptions->data as $sub) {
            if (
                in_array($sub->status, [
                    'active',
                    'trialing',
                    'incomplete',
                    'past_due',
                    'unpaid',
                ])
            ) {

                $stripeSub = StripeSubscription::retrieve($sub->id);
                $stripeSub->cancel();

                Log::warning('Canceled old Stripe subscription', [
                    'org_id' => $org->id,
                    'stripe_subscription_id' => $sub->id,
                ]);
            }
        }

        CashierSubscription::where('organization_id', $org->id)
            ->whereNull('ends_at')
            ->update([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ]);
    }



    /**
     * Update subscription plan
     */
    public function updateSubscription(
        CashierSubscription $subscription,
        Plan $newPlan
    ): ?CashierSubscription {
        try {
            if (!$newPlan->stripe_price_id) {
                $this->planService->sync($newPlan);
                $newPlan->refresh();
            }

            $stripeSub = StripeSubscription::retrieve($subscription->stripe_id);

            StripeSubscription::update($subscription->stripe_id, [
                'items' => [
                    [
                        'id' => $stripeSub->items->data[0]->id,
                        'price' => $newPlan->stripe_price_id,
                    ]
                ],
                'proration_behavior' => 'create_prorations',
            ]);

            $subscription->update([
                'stripe_price' => $newPlan->stripe_price_id,
            ]);

            $subscription->owner->update([
                'plan_id' => $newPlan->id,
            ]);

            return $subscription->fresh();

        } catch (\Exception $e) {
            Log::error('Failed to update subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(CashierSubscription $subscription, bool $immediately = false): bool
    {
        try {
            if ($immediately) {
                StripeSubscription::cancel($subscription->stripe_id);
                $subscription->update([
                    'stripe_status' => 'canceled',
                    'ends_at' => now(),
                ]);
            } else {
                StripeSubscription::update($subscription->stripe_id, [
                    'cancel_at_period_end' => true,
                ]);
                $subscription->update([
                    'ends_at' => Carbon::createFromTimestamp(
                        $subscription->asStripeSubscription()->current_period_end
                    ),
                ]);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Cancel failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get active subscription
     */
    public function getActiveSubscription(Organization $org): ?CashierSubscription
    {
        return $org->subscription('default');
    }

    /**
     * Trial ends on calendar boundary
     */
    protected function calculateTrialEndTimestamp(): int
    {
        $now = Carbon::now();

        $trialEnd = $now->day < 10
            ? $now->copy()->addMonth()->startOfMonth()
            : $now->copy()->endOfMonth();

        if ($trialEnd->lessThanOrEqualTo($now)) {
            $trialEnd = $now->addDay();
        }

        return $trialEnd->timestamp;
    }
}
