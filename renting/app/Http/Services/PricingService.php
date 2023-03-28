<?php

namespace App\Http\Services;

use App\Exceptions\ServiceOutOfOrderException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PricingService implements Service
{
    const NAME = 'pricing';
    const MAX_ATTEMPTS = 5;

    /**
     * @var PendingRequest
     */
    private $client;

    public function __construct(string $service)
    {
        $this->client = Http::baseUrl($service)
            ->accept('application/json')
            ->timeout(2);
    }

    public function getPeriod(string $identifier): ?array
    {
        try {
            if (RateLimiter::tooManyAttempts(self::NAME, self::MAX_ATTEMPTS)) {
                throw new ServiceOutOfOrderException();
            }

            $response = $this->client
                ->withToken(request()->bearerToken())
                ->get('/api/periods/' . $identifier)
                ->throwIfServerError();

            RateLimiter::clear(self::NAME);

            if ($response->clientError()) {
                return null;
            }

            return $response->json();
        } catch (HttpClientException $ex) {
            Log::info('could not get period: ' . $ex->getMessage(), ['id' => $identifier]);
            RateLimiter::hit(self::NAME);
            return null;
        } catch (ServiceOutOfOrderException) {
            Log::info(sprintf('%s service out of order', self::NAME));
            return null;
        }
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
