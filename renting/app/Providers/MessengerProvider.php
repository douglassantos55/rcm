<?php

namespace App\Providers;

use App\Messenger\Messenger;
use App\Messenger\RabbitMQMessenger;
use Illuminate\Support\ServiceProvider;

class MessengerProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(Messenger::class, function () {
            return new RabbitMQMessenger(
                env('MESSENGER_HOST'),
                env('MESSENGER_PORT'),
                env('MESSENGER_USERNAME'),
                env('MESSENGER_PASSWORD'),
            );
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
