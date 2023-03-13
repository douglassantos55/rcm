<?php

namespace App\Providers;

use App\Http\Services\InventoryService;
use App\Http\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function () {
            return new PaymentService(env('PAYMENT_SERVICE'));
        });

        $this->app->singleton(InventoryService::class, function () {
            return new InventoryService(env('INVENTORY_SERVICE'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
