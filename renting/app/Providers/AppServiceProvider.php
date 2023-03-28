<?php

namespace App\Providers;

use App\Http\Services\ConsulRegistry;
use App\Http\Services\InventoryService;
use App\Http\Services\PaymentService;
use App\Http\Services\PricingService;
use App\Http\Services\Registry;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function (Application $app) {
            $service = env('PAYMENT_SERVICE');
            $registry = $app->make(Registry::class);

            return new PaymentService($registry->get($service));
        });

        $this->app->singleton(InventoryService::class, function (Application $app) {
            $service = env('INVENTORY_SERVICE');
            $registry = $app->make(Registry::class);

            return new InventoryService($registry->get($service));
        });

        $this->app->singleton(PricingService::class, function (Application $app) {
            $service = env('PRICING_SERVICE');
            $registry = $app->make(Registry::class);

            return new PricingService($registry->get($service));
        });

        $this->app->singleton(Registry::class, function () {
            return new ConsulRegistry();
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
