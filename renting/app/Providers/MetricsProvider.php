<?php

namespace App\Providers;

use App\Metrics\Prometheus\Registry as PrometheusRegistry;
use App\Metrics\Registry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\Redis;

class MetricsProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Adapter::class, function () {
            return new Redis([
                'host' => env('REDIS_HOST'),
                'port' => env('REDIS_PORT'),
                'password' => env('REDIS_PASSWORD'),
            ]);
        });

        $this->app->singleton(Registry::class, function (Application $app) {
            $storage = $app->make(Adapter::class);
            return new PrometheusRegistry(new CollectorRegistry($storage));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
