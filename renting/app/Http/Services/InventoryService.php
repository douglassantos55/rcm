<?php

namespace App\Http\Services;

use App\Exceptions\ServiceOutOfOrderException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class InventoryService implements Service
{
    const MAX_ATTEMPTS = 5;
    const NAME = 'inventory';

    /** @var PendingRequest */
    private $client;

    public function __construct(string $serviceUrl)
    {
        $this->client = Http::baseUrl($serviceUrl)
            ->timeout(2)
            ->accept('application/json');
    }

    public function getEquipment(string $uuid): ?array
    {
        try {
            if (RateLimiter::tooManyAttempts(self::NAME, self::MAX_ATTEMPTS)) {
                throw new ServiceOutOfOrderException();
            }

            $response = $this->client
                ->get('/api/equipment/' . $uuid)
                ->throwIfServerError();

            RateLimiter::clear(self::NAME);

            if ($response->clientError()) {
                return null;
            }

            return $response->json();
        } catch (HttpClientException $ex) {
            Log::info('could not get equipment: ' . $ex->getMessage(), ['id' => $uuid]);
            RateLimiter::hit(self::NAME);
            return null;
        } catch (ServiceOutOfOrderException $ex) {
            Log::info(sprintf('%s service out of order', self::NAME));
            return null;
        }
    }

    public function has(string $entity, string $identifier): bool
    {
        return boolval($this->getEquipment($identifier));
    }
}
