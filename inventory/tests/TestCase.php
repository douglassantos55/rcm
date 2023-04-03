<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\InMemory;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(Adapter::class, InMemory::class);
        $this->withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyLCJpc3MiOiJ0ZXN0aW5nIiwiYXVkIjoidGVzdGluZyJ9.jq-HNs9D4J8Ujl7tOloioSLANqq0hRlPwZl4x-C60LY');
    }

    public function invalidTokensProvider()
    {
        return [
            // invalid issuer
            ['eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyLCJpc3MiOiJ0ZXN0IiwiYXVkIjoidGVzdGluZyJ9.gVZt9Ce3QpvuBgWdLs5m1JVYs39DHy0er2x-Ik8Do_8'],
            // invalid audience
            ['eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyLCJpc3MiOiJ0ZXN0aW5nIiwiYXVkIjoidGVzdCJ9.LyAUPkz_TUCxBJL3Do_2aw555DWcduBVt0spHLm1roQ'],
            // invalid algorithm
            ['eyJhbGciOiJIUzM4NCIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyLCJpc3MiOiJ0ZXN0aW5nIiwiYXVkIjoidGVzdGluZyJ9.ava9k70FhfRZ7y5svYZlFx1hymYw1BLZKBP_7WJ1Wmmoe1uNZieXrCTs1WsNNaCV'],
            // invalid issuer and audience
            ['eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyLCJpc3MiOiJ0ZXN0IiwiYXVkIjoidGVzdCJ9.uvTD0w-OXsXIaJM_4EWVm5j00dGjAOt7PmqSHzzld-8'],
            // invalid issuer, audience and algorithm
            ['eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyLCJpc3MiOiJ0ZXN0IiwiYXVkIjoidGVzdCJ9.4g2tdgZHB8qkWmHA1H0OX4U8x_EIO3rAR725jhkgSuoSEg16L2zGV7GAko-oUVn0n1RIQL5mSTmUJpQAQjQThw'],
        ];
    }
}
