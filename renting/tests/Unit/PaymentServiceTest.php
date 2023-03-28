<?php

namespace Tests\Unit;

use App\Http\Services\PaymentService;
use App\Http\Services\RateLimitBreaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_has()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'])]);
        $service = new PaymentService('payment', new RateLimitBreaker());

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertTrue($service->has('payment_type_id', $uuid));
        $this->assertTrue($service->has('payment_method_id', $uuid));
        $this->assertTrue($service->has('payment_condition_id', $uuid));
    }

    public function test_does_not_have()
    {
        Http::fake(['*' => Http::response('not found', 404)]);
        $service = new PaymentService('payment', new RateLimitBreaker());

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertFalse($service->has('payment_type_id', $uuid));
        $this->assertFalse($service->has('payment_method_id', $uuid));
        $this->assertFalse($service->has('payment_condition_id', $uuid));
    }

    public function test_has_server_error()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'], 500)]);
        $service = new PaymentService('payment', new RateLimitBreaker());

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertFalse($service->has('payment_type_id', $uuid));
        $this->assertFalse($service->has('payment_method_id', $uuid));
        $this->assertFalse($service->has('payment_condition_id', $uuid));
    }

    public function test_rate_limit_server_error_counts()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'], 500)]);
        $service = new PaymentService('payment', new RateLimitBreaker());

        for ($i = 0; $i < 5; $i++) {
            $this->assertNull($service->getPaymentCondition('aoeuaoeu'));
        }

        $this->assertNull($service->getPaymentCondition('aoeuaoeu'));
        Http::assertSentCount(5);
    }

    public function test_rate_limit_client_error_does_not_count()
    {
        Http::fake(['*' => Http::response('not found', 404)]);
        $service = new PaymentService('payment', new RateLimitBreaker());

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

        $service = new PaymentService('payment', new RateLimitBreaker());
        for ($i = 0; $i < 4; $i++) {
            $this->assertNull($service->getPaymentType('aoeu'));
        }

        $this->assertNotNull($service->getPaymentType($uuid));
        $this->assertEquals(5, RateLimiter::remaining($service::NAME, $service::MAX_ATTEMPTS));
    }

    public function test_forwards_jwt_token_payment_type()
    {
        Http::fake(['*' => Http::response()]);

        $service = new PaymentService('payment', new RateLimitBreaker());
        $service->getPaymentType('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }

    public function test_forwards_jwt_token_payment_method()
    {
        Http::fake(['*' => Http::response()]);

        $service = new PaymentService('payment', new RateLimitBreaker());
        $service->getPaymentMethod('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }

    public function test_forwards_jwt_token_payment_condition()
    {
        Http::fake(['*' => Http::response()]);

        $service = new PaymentService('payment', new RateLimitBreaker());
        $service->getPaymentCondition('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }
}
