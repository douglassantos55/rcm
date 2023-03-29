<?php

namespace App\Services\Rest;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\InventoryService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RestInventoryService implements InventoryService
{
    const MAX_ATTEMPTS = 5;
    const NAME = 'inventory';

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
            ->timeout(2)
            ->accept('application/json');
    }

    public function getEquipment(string $uuid): ?array
    {
        return $this->breaker->invoke(function () use ($uuid) {
            $response = $this->client
                ->withToken(request()->bearerToken())
                ->get('/api/equipment/' . $uuid)
                ->throwIfServerError();

            if ($response->clientError()) {
                return null;
            }

            return $response->json();
        }, self::NAME, self::MAX_ATTEMPTS);
    }

    public function has(string $entity, string $identifier): bool
    {
        return boolval($this->getEquipment($identifier));
    }
}
