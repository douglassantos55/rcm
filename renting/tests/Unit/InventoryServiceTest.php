<?php

namespace Tests\Unit;

use App\Services\CircuitBreaker\RateLimitBreaker;
use App\Services\Rest\RestInventoryService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    public function test_get_equipment_server_error()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $service = new RestInventoryService('inventory', new RateLimitBreaker());
        $equipment = $service->getEquipment('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        $this->assertNull($equipment);
    }

    public function test_get_equipment_client_error()
    {
        Http::fake(['*' => Http::response(null, 422)]);

        $service = new RestInventoryService('inventory', new RateLimitBreaker());
        $equipment = $service->getEquipment('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        $this->assertNull($equipment);
    }

    public function test_get_equipment_not_found()
    {
        Http::fake(['*' => Http::response(null, 404)]);

        $service = new RestInventoryService('inventory', new RateLimitBreaker());
        $equipment = $service->getEquipment('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        $this->assertNull($equipment);
    }

    public function test_get_equipment()
    {
        $uuid = 'ce283991-b0fb-4ea9-8286-f79157dfd3c1';
        Http::fake(['*' => Http::response(['id' => $uuid])]);

        $service = new RestInventoryService('inventory', new RateLimitBreaker());
        $equipment = $service->getEquipment($uuid);

        $this->assertNotNull($equipment);
    }

    public function test_rate_limiting_server_error()
    {
        $uuid = 'ce283991-b0fb-4ea9-8286-f79157dfd3c1';
        Http::fake(['*' => Http::response(null, 500)]);

        $service = new RestInventoryService('inventory', new RateLimitBreaker());
        for ($i = 0; $i < 5; $i++) {
            $service->getEquipment($uuid);
        }

        $service->getEquipment($uuid);
        Http::assertSentCount(5);
    }

    public function test_rate_limiting_client_error()
    {
        $uuid = 'ce283991-b0fb-4ea9-8286-f79157dfd3c1';
        Http::fake(['*' => Http::response(null, 404)]);

        $service = new RestInventoryService('inventory', new RateLimitBreaker());
        for ($i = 0; $i < 5; $i++) {
            $service->getEquipment($uuid);
        }

        $service->getEquipment($uuid);
        Http::assertSentCount(6);
    }

    public function test_rate_limiting_reset()
    {
        $uuid = 'ce283991-b0fb-4ea9-8286-f79157dfd3c1';

        Http::fake([
            'inventory/api/equipment/' . $uuid => Http::response(),
            'inventory/api/equipment/aoeu' => Http::response(null, 500),
        ]);

        $service = new RestInventoryService('inventory', new RateLimitBreaker());
        for ($i = 0; $i < 4; $i++) {
            $service->getEquipment('aoeu');
        }

        $service->getEquipment($uuid);
        $this->assertEquals(5, RateLimiter::remaining($service::NAME, $service::MAX_ATTEMPTS));
    }

    public function test_forwards_jwt_token()
    {
        Http::fake(['*' => Http::response()]);

        $service = new RestInventoryService('inventory', new RateLimitBreaker());
        $service->getEquipment('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization');
        });
    }
}
