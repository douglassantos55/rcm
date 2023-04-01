<?php

namespace App\Providers;

use App\Metrics\Prometheus\Registry as PrometheusRegistry;
use App\Metrics\Registry as MetricsRegistry;
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
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

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

        $this->app->singleton(Registry::class, ConsulRegistry::class);
        $this->app->singleton(CircuitBreaker::class, RateLimitBreaker::class);

        $this->app->singleton(MetricsRegistry::class, function () {
            $storage = new Redis([
                'host' => env('REDIS_HOST'),
                'port' => env('REDIS_PORT'),
                'password' => env('REDIS_PASSWORD'),
            ]);
            return new PrometheusRegistry(new CollectorRegistry($storage));
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
