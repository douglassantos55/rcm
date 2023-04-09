<?php

namespace App\Providers;

use App\Metrics\Prometheus\Registry as PrometheusRegistry;
use App\Metrics\Registry;
use App\Tracing\Tracer;
use App\Tracing\ZipkinTracer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\Redis;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
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

        $this->app->singleton(Tracer::class, function () {
            return new ZipkinTracer('pricing', env('ZIPKIN_ADDR'));
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
