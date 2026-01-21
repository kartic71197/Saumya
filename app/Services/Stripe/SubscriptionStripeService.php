<?php

namespace App\Services\Stripe;

use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Stripe\Exception\InvalidRequestException;

/**
 * Keeps Stripe Products & Prices in sync with Plan plans.
 *
 * DB is SSOT.
 */
class SubscriptionStripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Sync plan to Stripe (Product + Price).
     */
    public function sync(Plan $plan): void
    {
        try {
            if ($plan->stripe_product_id && $plan->stripe_price_id) {
                logger("Updating Stripe plan plan ID {$plan->stripe_product_id}");
                $this->update($plan);
                return;
            }
            logger("Creatig new Stripe plan plan ID {$plan->stripe_product_id}");
            $this->create($plan);
        } catch (\Exception $e) {
            Log::error('Stripe plan sync failed', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function create(Plan $plan): void
    {
        $product = Product::create([
            'name' => $plan->name,
            'description' => $plan->description,
            'active' => (bool) $plan->is_active,
            'metadata' => [
                'plan_id' => $plan->id,
            ],
        ]);

        $price = Price::create([
            'product' => $product->id,
            'unit_amount' => (int) ($plan->price * 100),
            'currency' => 'usd',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => (int) $plan->duration,
            ],
        ]);

        $plan->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id' => $price->id,
        ]);
    }

    protected function update(Plan $plan): void
    {
        try {
            Product::update($plan->stripe_product_id, [
                'name' => $plan->name,
                'description' => $plan->description,
                'active' => (bool) $plan->is_active,
            ]);
        } catch (InvalidRequestException $e) {
            if ($e->getStripeCode() === 'resource_missing') {
                $plan->update([
                    'stripe_product_id' => null,
                    'stripe_price_id' => null,
                ]);

                $this->create($plan);
                return;
            }

            throw $e;
        }

        // 🔍 Check for existing price first
        $existingPrice = $this->findExistingPrice($plan);

        if ($existingPrice) {
            logger("Reusing existing Stripe price {$existingPrice->id}");

            $plan->update([
                'stripe_price_id' => $existingPrice->id,
            ]);

            return;
        }

        // 🆕 Create new price only if needed
        $price = Price::create([
            'product' => $plan->stripe_product_id,
            'unit_amount' => (int) ($plan->price * 100),
            'currency' => 'usd',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => (int) $plan->duration,
            ],
        ]);

        $plan->update([
            'stripe_price_id' => $price->id,
        ]);
    }

    protected function findExistingPrice(Plan $plan): ?\Stripe\Price
    {
        $prices = Price::all([
            'product' => $plan->stripe_product_id,
            'active' => true,
            'limit' => 100, // Stripe max
        ]);

        foreach ($prices->data as $price) {
            if (
                $price->unit_amount === (int) ($plan->price * 100) &&
                $price->currency === 'usd' &&
                isset($price->recurring) &&
                $price->recurring->interval === 'month' &&
                $price->recurring->interval_count === (int) $plan->duration
            ) {
                return $price;
            }
        }

        return null;
    }


}
