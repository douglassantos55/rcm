<?php

namespace App\Http\Services;

use App\Exceptions\ServiceOutOfOrderException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PaymentService implements Service
{
    const NAME = 'payment';
    const MAX_ATTEMPTS = 5;

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
        try {
            if (RateLimiter::tooManyAttempts(self::NAME, self::MAX_ATTEMPTS)) {
                throw new ServiceOutOfOrderException();
            }

            $response = $this->client
                ->withToken(request()->bearerToken())
                ->get($url)
                ->throwIfServerError();

            RateLimiter::clear(self::NAME);

            if ($response->clientError()) {
                return null;
            }

            return $response->json();
        } catch (HttpClientException $ex) {
            Log::info('could not reach payment service: ' . $ex->getMessage(), ['url' => $url]);
            RateLimiter::hit(self::NAME);
            return null;
        } catch (ServiceOutOfOrderException $ex) {
            Log::info(sprintf('%s service out of order', self::NAME));
            return null;
        }
    }
}
