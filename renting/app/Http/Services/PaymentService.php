<?php

namespace App\Http\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PaymentService implements Service
{
    /**
     * @var PendingRequest
     */
    private $client;

    public function __construct(string $serviceUrl)
    {
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
        $response = $this->client->get($url);
        return $response->successful() ? $response->json() : null;
    }
}
