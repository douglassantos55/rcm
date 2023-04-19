<?php

namespace App\Services\Rest;

use App\Services\CircuitBreaker\CircuitBreaker;
use App\Services\InventoryService;
use App\Services\Tracing\Tracer;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RestInventoryService implements InventoryService
{
    const MAX_ATTEMPTS = 5;
    const NAME = 'inventory';

    /** @var PendingRequest */
    private $client;

    /**
     * @var CircuitBreaker
     */
    private $breaker;

    /**
     * @var Tracer
     */
    private $tracer;

    /**
     * @var Repository
     */
    private $cache;

    public function __construct(string $serviceUrl, CircuitBreaker $breaker, Tracer $tracer, Repository $cache)
    {
        $this->cache = $cache;
        $this->tracer = $tracer;
        $this->breaker = $breaker;

        $this->client = Http::baseUrl($serviceUrl)
            ->timeout(2)
            ->accept('application/json');
    }

    public function getEquipment(mixed $uuid): ?array
    {
        $cached = $this->getFromCache($uuid);

        if (!is_null($cached)) {
            return $cached;
        }

        return $this->breaker->invoke(function () use ($uuid) {
            $response = $this->tracer->trace('inventory.get_equipment', function ($context) use ($uuid) {
                if (is_array($uuid)) {
                    return $this->getMultiple($uuid, $context);
                }

                return $this->getSingle($uuid, $context);
            });

            if ($response->clientError()) {
                return null;
            }

            $items = $response->json();

            if (!is_array($uuid)) {
                $this->cache->put($uuid, $items, now()->addSeconds(5));
            } else {
                foreach ($items as $item) {
                    $this->cache->put($item['id'], $item, now()->addSeconds(5));
                }
            }

            return $items;
        }, self::NAME, self::MAX_ATTEMPTS);
    }

    public function has(string $entity, string $identifier): bool
    {
        return boolval($this->getEquipment($identifier));
    }

    private function getFromCache(mixed $uuid): ?array
    {
        if (is_array($uuid)) {
            $items = array_filter(array_map(fn ($id) => $this->cache->get($id), $uuid), 'is_array');

            if (empty($items)) {
                return null;
            }

            if (count($items) !== count($uuid)) {
                $missing = array_diff($uuid, array_keys($items));

                if (!empty($missing)) {
                    $items = array_merge($items, $this->getEquipment($missing));
                }
            }

            return $items;
        }

        return $this->cache->get($uuid);
    }

    private function getSingle(string $uuid, array $context = [])
    {
        return $this->request($context)
            ->get('/equipment/' . $uuid)
            ->throwIfServerError();
    }

    private function getMultiple(array $uuids, array $context = [])
    {
        $query = http_build_query(['uuids' => join(',', $uuids)]);

        return $this->request($context)
            ->get('/equipment?' . $query)
            ->throwIfServerError();
    }

    private function request(array $context = [])
    {
        return $this->client
            ->withHeaders($context)
            ->withToken(request()->bearerToken());
    }
}
