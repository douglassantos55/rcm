<?php

namespace App\Services\Rest;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\PricingService;
use App\Services\Tracing\Tracer;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RestPricingService implements PricingService
{
    const NAME = 'pricing';
    const MAX_ATTEMPTS = 5;

    /**
     * @var PendingRequest
     */
    private $client;

    /**
     * @var CircuitBreaker
     */
    private $breaker;


    /**
     * @var Tracer
     */
    private $tracer;

    public function __construct(string $service, CircuitBreaker $breaker, Tracer $tracer)
    {
        $this->tracer = $tracer;
        $this->breaker = $breaker;

        $this->client = Http::baseUrl($service)
            ->accept('application/json')
            ->timeout(2);
    }

    public function getPeriod(string $identifier): ?array
    {
        return $this->breaker->invoke(function () use ($identifier) {
            $response = $this->tracer->trace('pricing:get_period', function (array $context) use ($identifier) {
                return $this->client
                    ->withHeaders($context)
                    ->withToken(request()->bearerToken())
                    ->get('/api/periods/' . $identifier)
                    ->throwIfServerError();
            });

            if ($response->clientError()) {
                return null;
            }

            return $response->json();
        }, self::NAME, self::MAX_ATTEMPTS);
    }

    public function getRentingValues(string $equipment): ?array
    {
        return $this->client->get('/api/renting-values', [
            'equipment' => $equipment
        ])->json();
    }

    public function has(string $entity, string $identifier): bool
    {
        return boolval($this->getPeriod($identifier));
    }
}
