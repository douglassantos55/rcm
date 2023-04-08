<?php

namespace App\Providers;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\CircuitBreaker\RateLimitBreaker;
use App\Services\PricingService;
use App\Services\Registry\ConsulRegistry;
use App\Services\Registry\Registry;
use App\Services\Rest\RestPricingService;
use App\Services\Tracing\Tracer;
use App\Services\Tracing\ZipkinTracer;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PricingService::class, function (Application $app) {
            $service = env('PRICING_SERVICE');
            $registry = $app->make(Registry::class);
            $breaker = $app->make(CircuitBreaker::class);
            $tracer = $app->make(Tracer::class);

            return new RestPricingService($registry->get($service), $breaker, $tracer);
        });

        $this->app->singleton(Registry::class, function () {
            return new ConsulRegistry();
        });

        $this->app->singleton(Tracer::class, function () {
            return new ZipkinTracer('inventory', env('ZIPKIN_ADDR'));
        });

        $this->app->bind(CircuitBreaker::class, function (Application $app) {
            $limiter = $app->make(RateLimiter::class);
            $logger = $app->make(Logger::class);

            return new RateLimitBreaker($limiter, $logger);
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
