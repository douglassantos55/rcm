<?php

namespace App\Services\Tracing;

use Closure;

interface Tracer
{
    /**
     * @param string $name
     * @param Closure $callback fn (array $context): \Illuminate\Http\Response
     *
     * @return mixed
     */
    public function trace(string $name, Closure $callback): mixed;
}
