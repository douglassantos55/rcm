<?php

namespace Tests\Unit;

use App\Services\Balancer\RoundRobinBalancer;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Tests\TestCase;

class RoundRobinBalancerTest extends TestCase
{
    public function test_no_cache()
    {
        $repository = new Repository(new ArrayStore());
        $balancer = new RoundRobinBalancer($repository);

        $this->assertEquals('svc1', $balancer->get(['svc1', 'svc2', 'svc3']));
        $this->assertEquals('svc2', $balancer->get(['svc1', 'svc2', 'svc3']));
        $this->assertEquals('svc3', $balancer->get(['svc1', 'svc2', 'svc3']));
        $this->assertEquals('svc1', $balancer->get(['svc1', 'svc2', 'svc3']));
    }

    public function test_with_cache()
    {
        $repository = new Repository(new ArrayStore());
        $repository->put('instances', ['svc2', 'svc1']);

        $balancer = new RoundRobinBalancer($repository);

        $this->assertEquals('svc2', $balancer->get(['svc1', 'svc2']));
        $this->assertEquals('svc1', $balancer->get(['svc1', 'svc2']));
        $this->assertEquals('svc2', $balancer->get(['svc1', 'svc2']));
    }

    public function test_removed_instances()
    {
        $repository = new Repository(new ArrayStore());
        $repository->put('instances', ['svc2', 'svc1']);

        $balancer = new RoundRobinBalancer($repository);
        $this->assertEquals('svc1', $balancer->get(['svc1']));
    }

    public function test_new_instances()
    {
        $repository = new Repository(new ArrayStore());
        $repository->put('instances', ['svc2', 'svc1']);
        $balancer = new RoundRobinBalancer($repository);

        $this->assertEquals('svc2', $balancer->get(['svc1', 'svc2', 'svc3']));
        $this->assertEquals('svc1', $balancer->get(['svc1', 'svc2', 'svc3']));
        $this->assertEquals('svc2', $balancer->get(['svc1', 'svc2', 'svc3', 'svc4']));
        $this->assertEquals('svc3', $balancer->get(['svc1', 'svc2', 'svc3', 'svc4']));
        $this->assertEquals('svc1', $balancer->get(['svc1', 'svc2', 'svc3', 'svc4']));
        $this->assertEquals('svc2', $balancer->get(['svc1', 'svc2', 'svc3', 'svc4']));
        $this->assertEquals('svc4', $balancer->get(['svc1', 'svc2', 'svc3', 'svc4']));
    }

    public function test_no_instances()
    {
        $repository = new Repository(new ArrayStore());
        $balancer = new RoundRobinBalancer($repository);

        $this->assertThrows(fn () => $balancer->get([]));
    }

    public function test_all_instances_removed()
    {
        $repository = new Repository(new ArrayStore());
        $repository->put('instances', ['svc2', 'svc1']);
        $balancer = new RoundRobinBalancer($repository);

        $this->assertThrows(fn () => $balancer->get([]));
    }

    public function test_cached_non_existent()
    {
        $repository = new Repository(new ArrayStore());
        $repository->put('instances', ['svc2', 'svc1']);
        $balancer = new RoundRobinBalancer($repository);

        $this->assertEquals('svc3', $balancer->get(['svc3', 'svc4']));
        $this->assertEquals('svc4', $balancer->get(['svc3', 'svc4']));
        $this->assertEquals('svc3', $balancer->get(['svc3', 'svc4']));
    }
}
