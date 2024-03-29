<?php

namespace App\Services\Rest;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\PaymentService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Cache\Repository;

class RestPaymentService implements PaymentService
{
    const NAME = 'payment';
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
     * @var Repository
     */
    private $cache;

    public function __construct(string $serviceUrl, CircuitBreaker $breaker, Repository $cache)
    {
        $this->cache = $cache;
        $this->breaker = $breaker;

        $this->client = Http::baseUrl($serviceUrl)
            ->timeout(2)
            ->accept('application/json');
    }

    public function getPaymentType(string $id): ?array
    {
        return $this->request('/payment-types/' . $id);
    }

    public function getPaymentMethod(string $id): ?array
    {
        return $this->request('/payment-methods/' . $id);
    }

    public function getPaymentCondition(string $id): ?array
    {
        return $this->request('/payment-conditions/' . $id);
    }

    public function has(string $entity, string $identifier): bool
    {
        if (str($entity)->contains('payment_type')) {
            return boolval($this->getPaymentType($identifier));
        }

        if (str($entity)->contains('payment_method')) {
            return boolval($this->getPaymentMethod($identifier));
        }

        return boolval($this->getPaymentCondition($identifier));
    }

    private function request(string $url): ?array
    {
        if ($this->cache->has($url)) {
            return $this->cache->get($url);
        }

        return $this->breaker->invoke(function () use ($url) {
            $response = $this->client
                ->withToken(request()->bearerToken())
                ->get($url)
                ->throwIfServerError();

            if ($response->clientError()) {
                return null;
            }

            $result = $response->json();

            $this->cache->put($url, $result, now()->addSeconds(5));

            return $result;
        }, self::NAME, self::MAX_ATTEMPTS);
    }
}
