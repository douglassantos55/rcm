<?php

namespace App\Services\Rest;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\PricingService;
use App\Services\Tracing\Tracer;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class RestPricingService implements PricingService
{
    const MAX_ATTEMPTS = 5;
    const NAME = 'pricing';

    /** @var PendingRequest */
    private $client;

    /**
     * @var CircuitBreaker
     */
    private $breaker;

    /**
     * @var Tracer
     */
    private $tracer;

    public function __construct(string $serviceUrl, CircuitBreaker $breaker, Tracer $tracer)
    {
        $this->tracer = $tracer;
        $this->breaker = $breaker;

        $this->client = Http::baseUrl($serviceUrl)->acceptJson();
    }

    public function getRentingValues(string $equipment): array
    {
        return $this->breaker->invoke(function () use ($equipment) {
            return $this->tracer->trace('pricing:get_renting_values', function (array $context) use ($equipment) {
                $response = $this->client
                    ->timeout(2)
                    ->withHeaders($context)
                    ->withToken(request()->bearerToken())
                    ->get('/renting-values?equipment_id=' . $equipment);

                return $response->json() ? $response->json() : [];
            });
        }, self::NAME, self::MAX_ATTEMPTS);
    }

    public function createRentingValues(array $values): Response
    {
        $response = $this->breaker->invoke(function () use ($values) {
            return $this->tracer->trace('pricing:create_renting_values', function (array $context) use ($values) {
                $response = $this->client
                    ->timeout(2)
                    ->withHeaders($context)
                    ->withToken(request()->bearerToken())
                    ->post('/renting-values', ['values' => $values])
                    ->throwIfServerError();

                return response()->fromClient($response);
            });
        }, self::NAME, self::MAX_ATTEMPTS);

        if (is_null($response)) {
            return response('could not reach pricing service', 500);
        }

        return $response;
    }

    public function updateRentingValues(array $values): Response
    {
        $response = $this->breaker->invoke(function () use ($values) {
            return $this->tracer->trace('pricing:update_renting_values', function (array $context) use ($values) {
                $response = $this->client
                    ->timeout(2)
                    ->withHeaders($context)
                    ->withToken(request()->bearerToken())
                    ->put('/renting-values', ['values' => $values])
                    ->throwIfServerError();

                return response()->fromClient($response);
            });
        }, self::NAME, self::MAX_ATTEMPTS);

        if (is_null($response)) {
            return response('could not reach pricing service', 500);
        }

        return $response;
    }
}
