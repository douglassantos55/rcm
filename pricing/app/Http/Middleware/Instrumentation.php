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

    /**
     * @var Histogram
     */
    private $memoryUsage;

    public function __construct(Registry $registry)
    {
        $this->requestsCounter = $registry->getOrCreateCounter('total_requests', 'pricing', ['status']);
        $this->requestDuration = $registry->getOrCreateHistogram('request_duration_seconds', 'pricing', [], [1, 2, 3, 4, 5]);
        $this->memoryUsage = $registry->getOrCreateHistogram('memory_usage_mb', 'pricing', [], [5, 10, 15, 20, 30, 50, 100]);
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
        $this->memoryUsage->observe(memory_get_peak_usage(true) / 1000 / 1000);

        return $response;
    }
}
