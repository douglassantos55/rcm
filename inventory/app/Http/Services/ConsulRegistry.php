<?php

namespace App\Http\Services;

use Consul\Services\Agent;
use Consul\Services\Catalog;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ConsulRegistry implements Registry
{
    /** @var Agent */
    private $agent;

    /** @var Catalog */
    private $catalog;

    public function __construct()
    {
        $this->agent = new Agent();
        $this->catalog = new Catalog();
    }

    public function getAddress(string $id): string
    {
        try {
            $response = $this->catalog->service($id);
            return $response->getBody();
        } catch (RuntimeException $ex) {
            Log::error('could not get service: ' . $ex->getMessage(), ['id' => $id]);
            throw $ex;
        }
    }

    public function register(string $name): void
    {
        try {
            $response = $this->agent->registerService([
                'id' => $name,
                'name' => $name,
                'tags' => ['v1'],
                'address' => env('APP_URL'),
            ]);

            if (!$response->isSuccessful()) {
                throw new RuntimeException($response->getBody());
            }
        } catch (RuntimeException $ex) {
            Log::error('could not register service: ' . $ex->getMessage(), [
                'name' => $name,
                'address' => env('APP_URL'),
            ]);
            throw $ex;
        }
    }
}
