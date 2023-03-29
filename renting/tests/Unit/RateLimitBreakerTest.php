<?php

namespace Tests\Unit;

use App\Services\CircuitBreaker\RateLimitBreaker;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use Tests\TestCase;

class RateLimitBreakerTest extends TestCase
{
    /**
     * @var RateLimitBreaker
     */
    private $breaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->breaker = new RateLimitBreaker();
    }

    public function test_out_of_order()
    {
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit('test');
        }
        $this->assertNull($this->breaker->invoke(fn () => 3, 'test', 3));
    }

    public function test_out_of_order_wrong_service()
    {
        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit('service');
        }
        $this->assertEquals(3, $this->breaker->invoke(fn () => 3, 'test', 3));
    }

    public function test_decay()
    {
        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit('test', ($i + 1) * 60);
        }

        $this->travel(61)->seconds();
        $this->assertEquals(3, $this->breaker->invoke(fn () => 3, 'test', 3));
    }

    public function test_reset()
    {
        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit('test', ($i + 1) * 60);
        }

        $this->assertEquals(3, $this->breaker->invoke(fn () => 3, 'test', 4));
        $this->assertEquals(4, RateLimiter::remaining('test', 4));
    }

    public function test_callback_exception()
    {
        $callable = fn () => throw new RuntimeException('could not do something');

        $this->assertNull($this->breaker->invoke($callable, 'test', 3));
        $this->assertEquals(1, RateLimiter::attempts('test'));
    }
}
