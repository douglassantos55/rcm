<?php

namespace Tests\Unit;

use App\Http\Services\PaymentService;
use Illuminate\Support\Facades\Http;
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
        Http::fake(['*' => Http::response(['foo' => 'bar'], 404)]);
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
}
