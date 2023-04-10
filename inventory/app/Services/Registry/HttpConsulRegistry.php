<?php

namespace App\Services\Registry;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpConsulRegistry implements Registry
{
    /**
     * @var PendingRequest
     */
    private $client;

    public function __construct(string $url)
    {
        $this->client = Http::baseUrl($url)
            ->timeout(2)
            ->connectTimeout(2)
            ->acceptJson();
    }

    public function get(string $service): string
    {
        try {
            $response = $this->client->get('/v1/agent/health/service/name/' . $service)
                ->throw()
                ->json();

            foreach ($response as $service) {
                if ($service['AggregatedStatus'] === 'passing') {
                    return $service['Service']['Address'];
                }
            }

            throw new \Exception("service is not healthy");
        } catch (\Exception | RequestException $ex) {
            Log::error('could not get service', [
                'error' => $ex->getMessage(),
                'service' => $service,
            ]);
            throw $ex;
        }
    }
}
