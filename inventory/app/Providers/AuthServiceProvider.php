<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Auth\JwtGuard;
use App\Auth\JwtTokenDecoder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::extend('jwt', function (Application $app) {
            return new JwtGuard(
                $app->get(Request::class),
                $app->get(JwtTokenDecoder::class)
            );
        });
    }
}
