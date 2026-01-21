<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\Stripe\OrganizationSubscriptionStripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    protected OrganizationSubscriptionStripeService $subscriptionService;

    public function __construct(OrganizationSubscriptionStripeService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    // Admin view all plans
    public function index()
    {
        if (auth()->user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'You do not have permission to access this page.');
        }

        $plans = Plan::all();
        return view('admin.plans.index', compact('plans'));
    }

    // Show plan selection page
    public function create($planId)
    {
        $plan = Plan::findOrFail($planId);
        return view('plans.create', compact('plan'));
    }

    // Show pricing page for organization
    public function showPricing()
    {
        $plans = Plan::where('is_active', 1)->get();
        $org = auth()->user()->organization;

        $activeSubscription = $this->subscriptionService->getActiveSubscription($org);
        $activePlan = $org?->plan;

        logger('Plan page viewed', [
            'organization_id' => $org->id,
            'activeSubscription' => $activeSubscription
        ]);

        logger('Plan page viewed', [
            'organization_id' => $org->id,
            'active_plan_id' => $activePlan,
            'timestamp' => now()->toDateTimeString()
        ]);

        $currentPlan = [
            'name' => $activePlan?->name ?? 'NA',
            'expiry_date' => $org->plan_valid ?? now()->addDays(30),
            'max_users' => $activePlan?->max_users ?? 'N/A',
            'max_locations' => $activePlan?->max_locations ?? 'N/A',
            'price' => $activePlan?->price ?? 0,
            'duration' => $activePlan?->duration ?? 0,
        ];

        return view('organization.settings.plan_settings.index', [
            'plans' => $plans,
            'selectedPlan' => $activePlan?->id,
            'planDuration' => $activePlan?->duration ?? 0,
            'currentPlan' => $currentPlan,
            'currentPrice' => $currentPlan['price'],
        ]);
    }

    // Handle checkout / subscription creation or upgrade
    public function checkout(Request $request)
    {
        $org = auth()->user()->organization;
        $plan = Plan::findOrFail($request->plan_id);

        $activeSubscription = $this->subscriptionService->getActiveSubscription($org);

        // Prevent downgrades
        if ($activeSubscription && $plan->price < $activeSubscription->plan?->price) {
            return back()->with('error', 'Downgrades are not allowed.');
        }

        // Upgrade / change plan
        if ($activeSubscription && $plan->id != $activeSubscription->plan_id) {
            $subscription = $this->subscriptionService->updateSubscription($activeSubscription, $plan);
        } else {
            // New subscription
            $subscription = $this->subscriptionService->createSubscription($org, $plan);
        }

        if (!$subscription) {
            return back()->with('error', 'Subscription failed. Please try again.');
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Subscription processed successfully.');
    }

    // Redirect to Stripe billing portal
    public function billingPortal()
    {
        $org = auth()->user()->organization;

        if (!$org->stripe_id) {
            return back()->with('error', 'No Stripe customer found for this organization.');
        }

        $session = $org->stripe()->billingPortal->sessions->create([
            'customer' => $org->stripe_id,
            'return_url' => route('dashboard'),
        ]);

        return redirect($session->url);
    }
}
