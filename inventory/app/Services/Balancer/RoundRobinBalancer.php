<?php

namespace App\Services\Balancer;

use Exception;
use Illuminate\Contracts\Cache\Repository;

class RoundRobinBalancer implements Balancer
{
    /**
     * @var Repository
     */
    private $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function get(array $instances): string
    {
        if (empty($instances)) {
            $this->cache->delete('instances');
            throw new Exception('no instances available');
        }

        $available = $this->cache->get('instances', $instances);

        do {
            $instance = array_shift($available);

            // If cached are not present in instances, use instances
            if (is_null($instance)) {
                $available = $instances;
            }
        } while (false === array_search($instance, $instances));

        // Push it to the end of the queue
        array_push($available, $instance);

        // Add new instances
        array_push($available, ...array_diff($instances, $available));

        // Update the cache
        $this->cache->put('instances', $available);

        return $instance;
    }
}
