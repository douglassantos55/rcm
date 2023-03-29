<?php

namespace App\Services\CircuitBreaker;

interface CircuitBreaker
{
    public function invoke(callable $callback, string $service, int $maxAttempts): mixed;
}
