<?php

namespace App\Http\Services;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryService implements Service
{
    /** @var PendingRequest */
    private $client;

    public function __construct(string $serviceUrl)
    {
        $this->client = Http::baseUrl($serviceUrl)
            ->throw()
            ->timeout(2)
            ->accept('application/json');
    }

    public function getEquipment(string $uuid): ?array
    {
        try {
            return $this->client->get('/api/equipment/' . $uuid)->json();
        } catch (HttpClientException $ex) {
            Log::info('could not get equipment: ' . $ex->getMessage(), ['id' => $uuid]);
            return null;
        }
    }

    public function has(string $entity, string $identifier): bool
    {
        return boolval($this->getEquipment($identifier));
    }
}
