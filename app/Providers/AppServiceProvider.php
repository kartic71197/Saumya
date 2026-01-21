<?php

namespace App\Providers;

use App\Models\Organization;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\PurchaseOrder;
use App\Observers\PurchaseOrderObserver;
use App\Models\Edi810;
use App\Observers\Edi810Observer;
use App\Models\Payment;
use App\Observers\PaymentObserver;


use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    /**
     * WHY PurchaseOrderObserver IS REGISTERED HERE:
     * - Hooks into PurchaseOrder lifecycle events (create / update)
     * - Centralizes order-related notification logic
     * - Ensures notifications trigger automatically regardless of
     *   where the order is created or updated (controller, job, etc.)
     * - Prevents duplicated notification code across the application
     */
    public function boot(): void
    {
        PurchaseOrder::observe(PurchaseOrderObserver::class);
        Edi810::observe(Edi810Observer::class);
        Payment::observe(PaymentObserver::class);
        Cashier::useCustomerModel(Organization::class);
        // Disable MySQL strict mode and set default string length
        config()->set('database.connections.mysql.strict', false);
        Schema::defaultStringLength(191);

        // Share theme and organization session values with all views
        View::composer('*', function ($view) {
            $user = auth()->user(); // Get the authenticated user

            if ($user && $user->role_id == '1') {
                $view->with('themeClass', $user->theme_color ?? 'mustard');
                if ($user->organization) {
                    session([
                        'currency' => $user->organization->currency,
                        'timezone' => $user->organization->timezone,
                        'date_format' => $user->organization->date_format,
                        'time_format' => $user->organization->time_format,
                    ]);
                }
            } else {
                $view->with('themeClass', $user?->organization?->theme ?? 'mustard');
            }
        });
    }
}
