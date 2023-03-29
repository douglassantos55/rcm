<?php

namespace App\Providers;

use App\Http\Services\ConsulRegistry;
use App\Http\Services\PricingService;
use App\Http\Services\Registry;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Response as ClientResponse;
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
        Response::macro('fromClient', function (ClientResponse $response) {
            return Response::make(
                $response->body(),
                $response->status(),
                $response->headers()
            );
        });
    }
}
