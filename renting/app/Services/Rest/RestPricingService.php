<?php

namespace App\Services\Rest;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\PricingService;
use App\Services\Tracing\Tracer;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
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


    /**
     * @var Repository
     */
    private $cache;

    public function __construct(string $service, CircuitBreaker $breaker, Tracer $tracer, Repository $cache)
    {
        $this->tracer = $tracer;
        $this->breaker = $breaker;
        $this->cache = $cache;

        $this->client = Http::baseUrl($service)
            ->accept('application/json')
            ->timeout(2);
    }

    public function getPeriod(string $identifier): ?array
    {
        if ($this->cache->get('period_' . $identifier)) {
            return $this->cache->get('period_' . $identifier);
        }

        return $this->breaker->invoke(function () use ($identifier) {
            /** @var Response */
            $response = $this->tracer->trace('pricing:get_period', function (array $context) use ($identifier) {
                return $this->client
                    ->withHeaders($context)
                    ->withToken(request()->bearerToken())
                    ->get('/periods/' . $identifier)
                    ->throwIfServerError();
            });

            if ($response->clientError()) {
                return null;
            }

            $period = $response->json();
            $this->cache->put('period_' . $identifier, $period, now()->addMinute());

            return $period;
        }, self::NAME, self::MAX_ATTEMPTS);
    }

    public function getRentingValues(string $equipment): ?array
    {
        if ($this->cache->get('renting_values_' . $equipment)) {
            return $this->cache->get('renting_values_' . $equipment);
        }

        return $this->breaker->invoke(function () use ($equipment) {
            /** @var Response */
            $response = $this->tracer->trace('pricing:get_renting_values', function (array $context) use ($equipment) {
                return $this->client
                    ->withHeaders($context)
                    ->withToken(request()->bearerToken())
                    ->get('/renting-values', ['equipment' => $equipment])
                    ->throwIfServerError();
            });

            if ($response->clientError()) {
                return null;
            }

            $values = $response->json();
            $this->cache->put('renting_values_' . $equipment, $values, now()->addMinute());

            return $values;
        }, self::NAME, self::MAX_ATTEMPTS);
    }

    public function has(string $entity, string $identifier): bool
    {
        return boolval($this->getPeriod($identifier));
    }
}
