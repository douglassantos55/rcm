<?php

namespace App\Services\CircuitBreaker;

use App\Exceptions\ServiceOutOfOrderException;
use Exception;
use Illuminate\Cache\RateLimiter;
use Illuminate\Log\Logger;

class RateLimitBreaker implements CircuitBreaker
{
    /**
     * @var RateLimiter
     */
    private $limiter;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(RateLimiter $limiter, Logger $logger)
    {
        $this->logger = $logger;
        $this->limiter = $limiter;
    }

    public function invoke(callable $callback, string $service, int $attempts): mixed
    {
        try {
            if ($this->limiter->tooManyAttempts($service, $attempts)) {
                throw new ServiceOutOfOrderException();
            }

            $result = $callback();
            $this->limiter->clear($service);

            return $result;
        } catch (ServiceOutOfOrderException) {
            $this->logger->warning('Service out of order', [
                'service' => $service,
                'available_in' => $this->limiter->availableIn($service),
            ]);

            return null;
        } catch (Exception $ex) {
            $this->limiter->hit($service);

            $this->logger->error('Could not invoke service', [
                'service' => $service,
                'max_attempts' => $attempts,
                'attempts' => $this->limiter->attempts($service),
                'remaining' => $this->limiter->remaining($service, $attempts),
                'message' => $ex->getMessage(),
            ]);

            return null;
        }
    }
}
