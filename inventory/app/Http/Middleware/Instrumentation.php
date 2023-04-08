<?php

namespace App\Http\Middleware;

use App\Metrics\Counter;
use App\Metrics\Histogram;
use App\Metrics\Registry;
use App\Services\Tracing\Tracer;
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

    /**
     * @var Tracer
     */
    private $tracer;

    public function __construct(Registry $registry, Tracer $tracer)
    {
        $this->tracer = $tracer;
        $this->requestsCounter = $registry->getOrCreateCounter('total_requests', 'inventory', ['status']);
        $this->requestDuration = $registry->getOrCreateHistogram('request_duration_seconds', 'inventory', [], [1, 2, 3, 4, 5]);
        $this->memoryUsage = $registry->getOrCreateHistogram('memory_usage_mb', 'inventory', [], [5, 10, 15, 20, 30, 50, 100]);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = time();

        $response = $this->tracer->trace(
            $request->route()->getName(),
            fn () => $next($request)
        );

        $this->requestDuration->observe((time() - $start));
        $this->requestsCounter->increment([$response->status()]);
        $this->memoryUsage->observe(memory_get_peak_usage(true) / 1000 / 1000);

        return $response;
    }
}
