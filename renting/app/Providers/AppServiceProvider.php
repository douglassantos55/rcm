<?php

namespace App\Providers;

use App\Repositories\CustomerRepository;
use App\Repositories\EloquentCustomerRepository;
use App\Services\Balancer\Balancer;
use App\Services\Balancer\RoundRobinBalancer;
use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\CircuitBreaker\RateLimitBreaker;
use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\PricingService;
use App\Services\Registry\HttpConsulRegistry;
use App\Services\Registry\Registry;
use App\Services\Rest\RestInventoryService;
use App\Services\Rest\RestPaymentService;
use App\Services\Rest\RestPricingService;
use App\Services\Tracing\Tracer;
use App\Services\Tracing\ZipkinTracer;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        CustomerRepository::class => EloquentCustomerRepository::class,
    ];

    public $singletons = [
        CircuitBreaker::class => RateLimitBreaker::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Tracer::class, function () {
            return new ZipkinTracer('renting', env('ZIPKIN_ADDR'));
        });

        $this->app->singleton(Registry::class, function () {
            return new HttpConsulRegistry(env('CONSUL_HTTP_ADDR'));
        });

        $this->app->singleton(Balancer::class, function (Application $app) {
            return new RoundRobinBalancer($app->make(Repository::class));
        });

        $this->app->singleton(PaymentService::class, function (Application $app) {
            $registry = $app->make(Registry::class);
            $breaker = $app->make(CircuitBreaker::class);
            $balancer = $app->make(Balancer::class);

            $service = env('PAYMENT_SERVICE');
            $instance = $balancer->get($registry->get($service));

            return new RestPaymentService($instance, $breaker);
        });

        $this->app->singleton(InventoryService::class, function (Application $app) {
            $registry = $app->make(Registry::class);
            $breaker = $app->make(CircuitBreaker::class);
            $tracer = $app->make(Tracer::class);
            $balancer = $app->make(Balancer::class);
            $cache = $app->make(CacheRepository::class);

            $service = env('INVENTORY_SERVICE');
            $instance = $balancer->get($registry->get($service));

            return new RestInventoryService($instance, $breaker, $tracer, $cache);
        });

        $this->app->singleton(PricingService::class, function (Application $app) {
            $registry = $app->make(Registry::class);
            $breaker = $app->make(CircuitBreaker::class);
            $tracer = $app->make(Tracer::class);
            $balancer = $app->make(Balancer::class);

            $service = env('PRICING_SERVICE');
            $instance = $balancer->get($registry->get($service));

            return new RestPricingService($instance, $breaker, $tracer);
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
