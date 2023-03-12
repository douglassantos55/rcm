<?php

namespace App\Http\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class RentingService
{
    const MAX_ATTEMPTS = 5;
    const NAME = 'renting-service';

    /** @var PendingRequest */
    private $client;

    public function __construct(string $serviceUrl)
    {
        $this->client = Http::baseUrl($serviceUrl)
            ->withHeaders(['accept' => 'application/json']);
    }

    public function createRentingValues(array $values): Response
    {
        if (RateLimiter::tooManyAttempts(self::NAME, self::MAX_ATTEMPTS)) {
            return response('renting service out of order', 500);
        }

        try {
            $response = $this->client
                ->timeout(2)
                ->post('/api/renting-values', ['values' => $values])
                ->throwIfServerError();

            RateLimiter::clear(self::NAME);
            return response()->fromClient($response);
        } catch (\Exception $ex) {
            RateLimiter::hit(self::NAME);

            Log::error('could not create renting values: ' . $ex->getMessage());
            return response('could not reach renting service', 500);
        }
    }

    public function updateRentingValues(array $values)
    {
        if (RateLimiter::tooManyAttempts(self::NAME, self::MAX_ATTEMPTS)) {
            return response('renting service out of order', 500);
        }

        try {
            $response = $this->client
                ->timeout(2)
                ->put('/api/renting-values', ['values' => $values])
                ->throwIfServerError();

            RateLimiter::clear(self::NAME);
            return response()->fromClient($response);
        } catch (\Exception $ex) {
            RateLimiter::hit(self::NAME);

            Log::error('could not update renting values: ' . $ex->getMessage());
            return response('could not reach renting service', 500);
        }
    }
}