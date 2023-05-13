<?php

namespace App\Providers;

use App\Repositories\EloquentSupplierRepository;
use App\Repositories\SupplierRepository;
use App\Services\Balancer\Balancer;
use App\Services\Balancer\RoundRobinBalancer;
use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\CircuitBreaker\RateLimitBreaker;
use App\Services\PricingService;
use App\Services\Registry\HttpConsulRegistry;
use App\Services\Registry\Registry;
use App\Services\Rest\RestPricingService;
use App\Services\Tracing\Tracer;
use App\Services\Tracing\ZipkinTracer;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        SupplierRepository::class => EloquentSupplierRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PricingService::class, function (Application $app) {
            $registry = $app->make(Registry::class);
            $breaker = $app->make(CircuitBreaker::class);
            $balancer = $app->make(Balancer::class);
            $tracer = $app->make(Tracer::class);

            $service = env('PRICING_SERVICE');
            $instance = $balancer->get($registry->get($service));

            return new RestPricingService($instance, $breaker, $tracer);
        });

        $this->app->singleton(Tracer::class, function () {
            return new ZipkinTracer('inventory', env('ZIPKIN_ADDR'));
        });

        $this->app->bind(CircuitBreaker::class, function (Application $app) {
            $limiter = $app->make(RateLimiter::class);
            $logger = $app->make(Logger::class);

            return new RateLimitBreaker($limiter, $logger);
        });

        $this->app->singleton(Registry::class, function () {
            return new HttpConsulRegistry(env('CONSUL_HTTP_ADDR'));
        });

        $this->app->singleton(Balancer::class, function (Application $app) {
            return new RoundRobinBalancer($app->make(Repository::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('fromClient', function (ClientResponse $response) {
            return Response::make(
                $response->body(),
                $response->status(),
                $response->headers()
            );
        });
    }
}
