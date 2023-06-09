<?php

namespace Tests\Unit;

use App\Services\CircuitBreaker\RateLimitBreaker;
use App\Services\Rest\RestPaymentService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    /**
     * @var RateLimitBreaker
     */
    private $breaker;

    /**
     * @var RateLimiter
     */
    private $limiter;

    /**
     * @var Repository
     */
    private $cache;

    public function setUp(): void
    {
        parent::setUp();

        $this->limiter = app(RateLimiter::class);
        $this->cache = app(Repository::class);
        $this->breaker = new RateLimitBreaker($this->limiter, app(Logger::class));
    }

    public function test_has()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'])]);
        $service = new RestPaymentService('payment', $this->breaker, $this->cache);

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertTrue($service->has('payment_type_id', $uuid));
        $this->assertTrue($service->has('payment_method_id', $uuid));
        $this->assertTrue($service->has('payment_condition_id', $uuid));
    }

    public function test_does_not_have()
    {
        Http::fake(['*' => Http::response('not found', 404)]);
        $service = new RestPaymentService('payment', $this->breaker, $this->cache);

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertFalse($service->has('payment_type_id', $uuid));
        $this->assertFalse($service->has('payment_method_id', $uuid));
        $this->assertFalse($service->has('payment_condition_id', $uuid));
    }

    public function test_has_server_error()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'], 500)]);
        $service = new RestPaymentService('payment', $this->breaker, $this->cache);

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertFalse($service->has('payment_type_id', $uuid));
        $this->assertFalse($service->has('payment_method_id', $uuid));
        $this->assertFalse($service->has('payment_condition_id', $uuid));
    }

    public function test_rate_limit_server_error_counts()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'], 500)]);
        $service = new RestPaymentService('payment', $this->breaker, $this->cache);

        for ($i = 0; $i < 5; $i++) {
            $this->assertNull($service->getPaymentCondition('aoeuaoeu'));
        }

        $this->assertNull($service->getPaymentCondition('aoeuaoeu'));
        Http::assertSentCount(5);
    }

    public function test_rate_limit_client_error_does_not_count()
    {
        Http::fake(['*' => Http::response('not found', 404)]);
        $service = new RestPaymentService('payment', $this->breaker, $this->cache);

        for ($i = 0; $i < 5; $i++) {
            $this->assertNull($service->getPaymentCondition('aoeuaoeu'));
        }

        $this->assertNull($service->getPaymentCondition('aoeuaoeu'));
        Http::assertSentCount(6);
    }

    public function test_rate_limit_reset_when_successful()
    {
        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';

        Http::fake([
            'payment/payment-types/aoeu' => Http::response('error', 500),
            'payment/payment-types/' . $uuid => Http::response(['id' => '123']),
        ]);

        $service = new RestPaymentService('payment', $this->breaker, $this->cache);
        for ($i = 0; $i < 4; $i++) {
            $this->assertNull($service->getPaymentType('aoeu'));
        }

        $this->assertNotNull($service->getPaymentType($uuid));
        $this->assertEquals(5, $this->limiter->remaining($service::NAME, $service::MAX_ATTEMPTS));
    }

    public function test_forwards_jwt_token_payment_type()
    {
        Http::fake(['*' => Http::response()]);

        $service = new RestPaymentService('payment', $this->breaker, $this->cache);
        $service->getPaymentType('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }

    public function test_forwards_jwt_token_payment_method()
    {
        Http::fake(['*' => Http::response()]);

        $service = new RestPaymentService('payment', $this->breaker, $this->cache);
        $service->getPaymentMethod('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }

    public function test_forwards_jwt_token_payment_condition()
    {
        Http::fake(['*' => Http::response()]);

        $service = new RestPaymentService('payment', $this->breaker, $this->cache);
        $service->getPaymentCondition('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }
}
