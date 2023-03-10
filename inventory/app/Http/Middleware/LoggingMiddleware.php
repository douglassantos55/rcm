<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('request', [
            'url' => $request->url(),
            'payload' => $request->json()->all(),
            'headers' => $request->headers->all(),
        ]);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        Log::info('response', [
            'status' => $response->getStatusCode(),
            'content' => $response->getContent(),
            'headers' => $response->headers->all(),
        ]);
    }
}
