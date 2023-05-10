<?php

namespace Tests\Unit;

use App\Services\CircuitBreaker\RateLimitBreaker;
use App\Services\Rest\RestPricingService;
use App\Services\Tracing\Tracer;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    /**
     * @var RateLimiter
     */
    private $limiter;

    /**
     * @var RateLimitBreaker
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

    public function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();

        $this->tracer = app(Tracer::class);
        $this->limiter = app(RateLimiter::class);
        $this->cache = app(Repository::class);
        $this->breaker = new RateLimitBreaker($this->limiter, app(Logger::class));
    }

    public function test_get_period_server_error()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);
        $this->assertNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));
    }

    public function test_get_period_client_error()
    {
        Http::fake(['*' => Http::response(['foo' => 'client'], 404)]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);
        $this->assertNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));
    }

    public function test_get_period()
    {
        $uuid = '04fedb8b-87c3-44a0-9b42-b4043a7afe8a';

        Http::fake([
            'pricing/periods/' . $uuid => Http::response(['foo' => 'bar']),
            'pricing/periods/*' => Http::response(['foo' => 'check'], 404),
        ]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);
        $this->assertNotNull($service->getPeriod($uuid));
        $this->assertNull($service->getPeriod('5b1721e4-6841-48aa-a785-c06ff5317f4d'));
    }

    public function test_get_period_cached()
    {
        Http::fake([
            'pricing/periods/*' => Http::response(['foo' => 'bar']),
        ]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);

        $this->assertNotNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));
        $this->assertNotNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));
        $this->assertNotNull($service->getPeriod('f0781d6b-4bd5-4128-b2db-24ca27915db7'));
        $this->assertNotNull($service->getPeriod('f0781d6b-4bd5-4128-b2db-24ca27915db7'));

        Http::assertSentCount(2);
    }

    public function test_get_period_cache_expires()
    {
        Http::fake([
            'pricing/periods/*' => Http::response(['foo' => 'bar']),
        ]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);

        $this->assertNotNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));
        $this->assertNotNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));

        $this->travel(2)->minutes();

        $this->assertNotNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));
        $this->assertNotNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));

        Http::assertSentCount(2);
    }

    public function test_get_period_forwards_token()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'])]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);
        $service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }

    public function test_circuit_breaker_server_error()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $uuid = '04fedb8b-87c3-44a0-9b42-b4043a7afe8a';
        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);

        for ($i = 0; $i < 5; $i++) {
            $this->assertNull($service->getPeriod($uuid));
        }

        $this->assertNull($service->getPeriod($uuid));
        Http::assertSentCount(5);
    }

    public function test_circuit_breaker_client_error()
    {
        Http::fake(['*' => Http::response(['foo' => 'breaker'], 404)]);

        $uuid = '04fedb8b-87c3-44a0-9b42-b4043a7afe8a';
        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);

        for ($i = 0; $i < 5; $i++) {
            $this->assertNull($service->getPeriod($uuid));
        }

        $this->assertNull($service->getPeriod($uuid));
        Http::assertSentCount(6);
    }

    public function test_circuit_breaker_reset()
    {
        $uuid = '04fedb8b-87c3-44a0-9b42-b4043a7afe8a';

        Http::fake([
            'pricing/periods/' . $uuid => Http::response(['foo' => 'ok']),
            'pricing/periods/*' => Http::response(null, 500),
        ]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);
        for ($i = 0; $i < 4; $i++) {
            $this->assertNull($service->getPeriod('something'));
        }

        $this->assertNotNull($service->getPeriod($uuid));

        $remaining = $this->limiter->remaining($service::NAME, $service::MAX_ATTEMPTS);
        $this->assertEquals(5, $remaining);
    }

    public function test_get_renting_values()
    {
        Http::fake(['pricing/renting-values*' => Http::response(['id' => 'aoeu'])]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);
        $this->assertNotNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));
    }

    public function test_get_renting_values_client_error()
    {
        Http::fake(['pricing/renting-values*' => Http::response(null, 404)]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);
        $this->assertNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));
    }

    public function test_get_renting_values_server_error()
    {
        Http::fake(['pricing/renting-values*' => Http::response(null, 500)]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);
        $this->assertNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));
    }

    public function test_get_renting_values_cache()
    {
        Http::fake(['pricing/renting-values*' => Http::response(['id' => 'aoeu'])]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);

        $this->assertNotNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));
        $this->assertNotNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));

        $this->assertNotNull($service->getRentingValues('0bf291b5-b1e6-479e-a662-1b58f82093a4'));
        $this->assertNotNull($service->getRentingValues('0bf291b5-b1e6-479e-a662-1b58f82093a4'));

        Http::assertSentCount(2);
    }

    public function test_get_renting_values_cache_expires()
    {
        Http::fake(['pricing/renting-values*' => Http::response(['id' => 'aoeu'])]);

        $service = new RestPricingService('pricing', $this->breaker, $this->tracer, $this->cache);

        $this->assertNotNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));
        $this->assertNotNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));

        $this->travel(2)->minutes();

        $this->assertNotNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));
        $this->assertNotNull($service->getRentingValues('993da6a0-beea-4dd5-9e52-f3a669ccfd20'));

        Http::assertSentCount(2);
    }
}
