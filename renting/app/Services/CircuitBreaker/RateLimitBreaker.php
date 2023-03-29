<?php

namespace App\Services\CircuitBreaker;

use App\Exceptions\ServiceOutOfOrderException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitBreaker implements CircuitBreaker
{
    public function invoke(callable $callback, string $service, int $attempts): mixed
    {
        try {
            if (RateLimiter::tooManyAttempts($service, $attempts)) {
                throw new ServiceOutOfOrderException();
            }

            $result = $callback();
            RateLimiter::clear($service);

            return $result;
        } catch (ServiceOutOfOrderException) {
            Log::warning(sprintf('%s service out of order', $service));
            return null;
        } catch (Exception $ex) {
            Log::error('could not invoke service: ' . $ex->getMessage());
            RateLimiter::hit($service);
            return null;
        }
    }
}
