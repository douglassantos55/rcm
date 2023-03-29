<?php

namespace Tests\Unit;

use App\Services\CircuitBreaker\RateLimitBreaker;
use App\Services\Rest\RestPricingService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    public function test_get_period_server_error()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $service = new RestPricingService('pricing', new RateLimitBreaker());
        $this->assertNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));
    }

    public function test_get_period_client_error()
    {
        Http::fake(['*' => Http::response(['foo' => 'client'], 404)]);

        $service = new RestPricingService('pricing', new RateLimitBreaker());
        $this->assertNull($service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a'));
    }

    public function test_get_period()
    {
        $uuid = '04fedb8b-87c3-44a0-9b42-b4043a7afe8a';

        Http::fake([
            'pricing/api/periods/' . $uuid => Http::response(['foo' => 'bar']),
            'pricing/api/periods/*' => Http::response(['foo' => 'check'], 404),
        ]);

        $service = new RestPricingService('pricing', new RateLimitBreaker());
        $this->assertNotNull($service->getPeriod($uuid));
        $this->assertNull($service->getPeriod('5b1721e4-6841-48aa-a785-c06ff5317f4d'));
    }

    public function test_get_period_forwards_token()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'])]);

        $service = new RestPricingService('pricing', new RateLimitBreaker());
        $service->getPeriod('04fedb8b-87c3-44a0-9b42-b4043a7afe8a');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }

    public function test_circuit_breaker_server_error()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $uuid = '04fedb8b-87c3-44a0-9b42-b4043a7afe8a';
        $service = new RestPricingService('pricing', new RateLimitBreaker());

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
        $service = new RestPricingService('pricing', new RateLimitBreaker());

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
            'pricing/api/periods/' . $uuid => Http::response(['foo' => 'ok']),
            'pricing/api/periods/*' => Http::response(null, 500),
        ]);

        $service = new RestPricingService('pricing', new RateLimitBreaker());
        for ($i = 0; $i < 4; $i++) {
            $this->assertNull($service->getPeriod('something'));
        }

        $this->assertNotNull($service->getPeriod($uuid));

        $remaining = RateLimiter::remaining($service::NAME, $service::MAX_ATTEMPTS);
        $this->assertEquals(5, $remaining);
    }
}
