<?php

namespace App\Services\Rest;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\PricingService;
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

    public function __construct(string $serviceUrl, CircuitBreaker $breaker)
    {
        $this->breaker = $breaker;

        $this->client = Http::baseUrl($serviceUrl)
            ->accept('application/json');
    }

    public function createRentingValues(array $values): Response
    {
        $response = $this->breaker->invoke(function () use ($values) {
            $response = $this->client
                ->timeout(2)
                ->withToken(request()->bearerToken())
                ->post('/api/renting-values', ['values' => $values])
                ->throwIfServerError();

            return response()->fromClient($response);
        }, self::NAME, self::MAX_ATTEMPTS);

        if (is_null($response)) {
            return response('could not reach renting service', 500);
        }

        return $response;
    }

    public function updateRentingValues(array $values): Response
    {
        $response = $this->breaker->invoke(function () use ($values) {
            $response = $this->client
                ->timeout(2)
                ->withToken(request()->bearerToken())
                ->put('/api/renting-values', ['values' => $values])
                ->throwIfServerError();

            return response()->fromClient($response);
        }, self::NAME, self::MAX_ATTEMPTS);

        if (is_null($response)) {
            return response('could not reach renting service', 500);
        }

        return $response;
    }
}
