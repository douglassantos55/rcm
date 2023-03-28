<?php

namespace App\Http\Services;

interface CircuitBreaker
{
    public function invoke(callable $callback, string $service, int $attempts): mixed;
}
