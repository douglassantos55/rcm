<?php

namespace Tests\Unit;

use App\Http\Services\PaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_has()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'])]);
        $service = new PaymentService('payment');

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertTrue($service->has('payment_type_id', $uuid));
        $this->assertTrue($service->has('payment_method_id', $uuid));
        $this->assertTrue($service->has('payment_condition_id', $uuid));
    }

    public function test_does_not_have()
    {
        Http::fake(['*' => Http::response('not found', 404)]);
        $service = new PaymentService('payment');

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertFalse($service->has('payment_type_id', $uuid));
        $this->assertFalse($service->has('payment_method_id', $uuid));
        $this->assertFalse($service->has('payment_condition_id', $uuid));
    }

    public function test_has_server_error()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'], 500)]);
        $service = new PaymentService('payment');

        $uuid = '2569e8d3-0ccf-4915-9fba-21cb2dddc7b5';
        $this->assertFalse($service->has('payment_type_id', $uuid));
        $this->assertFalse($service->has('payment_method_id', $uuid));
        $this->assertFalse($service->has('payment_condition_id', $uuid));
    }

    public function test_rate_limit_server_error_counts()
    {
        Http::fake(['*' => Http::response(['foo' => 'bar'], 500)]);
        $service = new PaymentService('payment');

        for ($i = 0; $i < 5; $i++) {
            $this->assertNull($service->getPaymentCondition('aoeuaoeu'));
        }

        $this->assertNull($service->getPaymentCondition('aoeuaoeu'));
        Http::assertSentCount(5);
    }

    public function test_rate_limit_client_error_does_not_count()
    {
        Http::fake(['*' => Http::response('not found', 404)]);
        $service = new PaymentService('payment');

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

        $service = new PaymentService('payment');
        for ($i = 0; $i < 4; $i++) {
            $this->assertNull($service->getPaymentType('aoeu'));
        }

        $this->assertNotNull($service->getPaymentType($uuid));
        $this->assertEquals(5, RateLimiter::remaining($service::NAME, $service::MAX_ATTEMPTS));
    }
}
