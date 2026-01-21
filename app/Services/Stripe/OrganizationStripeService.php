<?php

namespace App\Services\Stripe;

use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Exception\InvalidRequestException;

/**
 * Class OrganizationStripeService
 *
 * Handles syncing Organization data with Stripe Customer.
 *
 * DB remains the Single Source of Truth (SSOT).
 * Stripe mirrors Organization data.
 */
class OrganizationStripeService
{

    /**
     * Initialize Stripe with secret key.
     */

    public function __construct(

    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create or update Stripe Customer for the given Organization.
     *
     * - If stripe_id exists → update customer
     * - If stripe_id exists but customer not found on Stripe → recreate
     * - If stripe_id does not exist → create customer
     *
     * @param  Organization  $org
     * @return Customer|null
     */
    public function syncOrganization(Organization $org): ?Customer
    {
        try {
            $customer = $org->stripe_id
                ? $this->updateCustomer($org)
                : $this->createCustomer($org);
            $this->syncSubscription($org);
            return $customer;
        } catch (\Exception $e) {
            Log::error('Stripe organization sync failed', [
                'org_id' => $org->id,
                'stripe_id' => $org->stripe_id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function syncSubscription(Organization $org): void
    {
        try {
            $plan = $org->plan;

            if (!$plan) {
                Log::info("Organization {$org->id} has no plan, skipping subscription sync.");
                return;
            }

            // 1️⃣ Ensure plan exists on Stripe
            if (!$plan->stripe_product_id || !$plan->stripe_price_id) {
                app(SubscriptionStripeService::class)->sync($plan);
                $plan->refresh();
            }

            // 2️⃣ Subscription logic MUST use OrganizationSubscriptionStripeService
            $subscriptionService = app(OrganizationSubscriptionStripeService::class);

            $subscription = $subscriptionService->getActiveSubscription($org);

            if (!$subscription) {
                // No subscription → create
                logger("Creating subscription for org {$org->id} on plan {$plan->id}");
                $subscriptionService->createSubscription($org, $plan);
                Log::info("Subscription created for org {$org->id} on plan {$plan->id}");
                return;
            }

            // 3️⃣ Plan mismatch → update
            if ($subscription->plan_id !== $plan->id) {
                logger("updating subscription for org {$org->id} on plan {$plan->id}");
                $subscriptionService->updateSubscription($subscription, $plan);
                Log::info("Subscription updated for org {$org->id} to plan {$plan->id}");
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync subscription for organization', [
                'org_id' => $org->id,
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Create a new Stripe Customer and attach it to the Organization.
     *
     * @param  Organization  $org
     * @return Customer
     */
    protected function createCustomer(Organization $org): Customer
    {
        $customer = Customer::create([
            'name' => $org->name,
            'email' => $org->email,
            'phone' => $org->phone,
            'address' => [
                'line1' => $org->address,
                'city' => $org->city,
                'state' => $org->state,
                'postal_code' => $org->pin,
                'country' => $org->country,
            ],
            'metadata' => [
                'organization_id' => $org->id,
                'organization_code' => $org->organization_code,
            ],
        ]);

        // Persist Stripe customer ID in DB (SSOT)
        $org->update([
            'stripe_id' => $customer->id,
        ]);

        return $customer;
    }

    /**
     * Update existing Stripe Customer.
     *
     * If the Stripe customer does not exist (deleted from Stripe),
     * it will recreate the customer and re-sync the stripe_id.
     *
     * @param  Organization  $org
     * @return Customer
     */
    protected function updateCustomer(Organization $org): Customer
    {
        try {
            return Customer::update($org->stripe_id, [
                'name' => $org->name,
                'email' => $org->email,
                'phone' => $org->phone,
                'address' => [
                    'line1' => $org->address,
                    'city' => $org->city,
                    'state' => $org->state,
                    'postal_code' => $org->pin,
                    'country' => $org->country,
                ],
            ]);
        } catch (InvalidRequestException $e) {
            /**
             * Handle case where stripe_id exists in DB
             * but customer is missing/deleted on Stripe.
             *
             * This ensures SSOT consistency.
             */
            if ($e->getStripeCode() === 'resource_missing') {
                Log::warning('Stripe customer missing, recreating', [
                    'org_id' => $org->id,
                    'stripe_id' => $org->stripe_id,
                ]);

                // Clear invalid stripe_id
                $org->update(['stripe_id' => null]);

                // Recreate Stripe customer
                return $this->createCustomer($org);
            }

            throw $e;
        }
    }
}
