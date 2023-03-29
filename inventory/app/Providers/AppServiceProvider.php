<?php

namespace App\Providers;

use App\Services\PricingService;
use App\Services\Registry\ConsulRegistry;
use App\Services\Registry\Registry;
use App\Services\Rest\RestPricingService;
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

            return new RestPricingService($registry->get($service));
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
