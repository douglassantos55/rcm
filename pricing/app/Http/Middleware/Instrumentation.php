<?php

namespace App\Http\Middleware;

use App\Metrics\Counter;
use App\Metrics\Histogram;
use App\Metrics\Registry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Instrumentation
{
    /**
     * @var Counter
     */
    private $requestsCounter;

    /**
     * @var Histogram
     */
    private $requestDuration;

    public function __construct(Registry $registry)
    {
        $this->requestsCounter = $registry->getOrCreateCounter('total_requests', 'renting', ['status']);
        $this->requestDuration = $registry->getOrCreateHistogram('request_duration_seconds', 'renting', [], [1, 2, 3, 4, 5]);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = time();
        $response = $next($request);

        $this->requestDuration->observe((time() - $start));
        $this->requestsCounter->increment([$response->status()]);

        return $response;
    }
}
