<?php

namespace App\Providers;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\CircuitBreaker\RateLimitBreaker;
use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\PricingService;
use App\Services\Registry\ConsulRegistry;
use App\Services\Registry\Registry;
use App\Services\Rest\RestInventoryService;
use App\Services\Rest\RestPaymentService;
use App\Services\Rest\RestPricingService;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        CircuitBreaker::class => RateLimitBreaker::class,
        Registry::class => ConsulRegistry::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function (Application $app) {
            $service = env('PAYMENT_SERVICE');
            $registry = $app->make(Registry::class);
            $breaker = $app->make(CircuitBreaker::class);

            return new RestPaymentService($registry->get($service), $breaker);
        });

        $this->app->singleton(InventoryService::class, function (Application $app) {
            $service = env('INVENTORY_SERVICE');
            $registry = $app->make(Registry::class);
            $breaker = $app->make(CircuitBreaker::class);

            return new RestInventoryService($registry->get($service), $breaker);
        });

        $this->app->singleton(PricingService::class, function (Application $app) {
            $service = env('PRICING_SERVICE');
            $registry = $app->make(Registry::class);
            $breaker = $app->make(CircuitBreaker::class);

            return new RestPricingService($registry->get($service), $breaker);
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
