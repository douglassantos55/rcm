<?php

namespace App\Providers;

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
        //
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
