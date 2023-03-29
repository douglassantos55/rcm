<?php

namespace App\Services\Registry;

use Consul\Services\Catalog;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ConsulRegistry implements Registry
{
    /** @var Catalog */
    private $catalog;

    public function __construct()
    {
        $this->catalog = new Catalog();
    }

    public function get(string $service): string
    {
        try {
            $response = $this->catalog->service($service)->json();
            return $response[0]['ServiceAddress'];
        } catch (RuntimeException $ex) {
            Log::error('could not get service: ' . $ex->getMessage(), ['service' => $service]);
            throw $ex;
        }
    }
}
