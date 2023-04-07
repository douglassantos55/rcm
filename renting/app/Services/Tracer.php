<?php

namespace App\Services;

interface Tracer
{
    /**
     * @param callable $callback fn (array $context): mixed
     *
     * @return mixed
     */
    public function trace(callable $callback): mixed;
}
