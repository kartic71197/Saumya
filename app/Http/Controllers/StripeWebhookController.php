<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhook;
use App\Services\Stripe\OrganizationSubscriptionStripeService;

class StripeWebhookController extends CashierWebhook
{
    protected OrganizationSubscriptionStripeService $subscriptionService;

    public function __construct(OrganizationSubscriptionStripeService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function __invoke(Request $request)
    {
        return parent::handleWebhook($request);
    }

    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        $this->syncSubscription($payload);
    }

    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $this->syncSubscription($payload);
    }

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        $subscription = $payload['data']['object'];

        $org = Organization::where('stripe_id', $subscription['customer'])->first();
        if (!$org) return;

        $localSubscription = $org->subscriptions()
            ->where('stripe_subscription_id', $subscription['id'])
            ->first();

        if ($localSubscription) {
            $localSubscription->update(['status' => 'canceled', 'ended_at' => now()]);
        }

        Log::info('Subscription cancelled – plan revoked', [
            'organization_id' => $org->id,
            'stripe_subscription_id' => $subscription['id'],
        ]);
    }

    private function syncSubscription(array $payload)
    {
        $subscriptionData = $payload['data']['object'];

        $org = Organization::where('stripe_id', $subscriptionData['customer'])->first();
        if (!$org) return;

        $priceId = $subscriptionData['items']['data'][0]['price']['id'] ?? null;
        if (!$priceId) return;

        $plan = Plan::where('stripe_price_id', $priceId)->first();
        if (!$plan) return;

        $localSubscription = $org->subscriptions()
            ->where('stripe_subscription_id', $subscriptionData['id'])
            ->first();

        if ($localSubscription) {
            $this->subscriptionService->syncSubscriptionStatus($localSubscription);
        } else {
            // Create a local subscription if not exists
            $org->subscriptions()->create([
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $subscriptionData['id'],
                'status' => $subscriptionData['status'],
                'current_period_start' => $subscriptionData['current_period_start'] ? \Carbon\Carbon::createFromTimestamp($subscriptionData['current_period_start']) : null,
                'current_period_end' => $subscriptionData['current_period_end'] ? \Carbon\Carbon::createFromTimestamp($subscriptionData['current_period_end']) : null,
                'trial_start' => $subscriptionData['trial_start'] ? \Carbon\Carbon::createFromTimestamp($subscriptionData['trial_start']) : null,
                'trial_end' => $subscriptionData['trial_end'] ? \Carbon\Carbon::createFromTimestamp($subscriptionData['trial_end']) : null,
            ]);
        }

        // Update organization's current plan
        $org->update(['plan_id' => $plan->id]);

        Log::info('Organization plan synced from Stripe', [
            'organization_id' => $org->id,
            'plan_id' => $plan->id,
            'stripe_subscription_id' => $subscriptionData['id'],
        ]);
    }
}
