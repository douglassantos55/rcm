<?php

namespace Tests\Unit;

use App\Http\Services\InventoryService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    public function test_get_equipment_server_error()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $service = new InventoryService('inventory');
        $equipment = $service->getEquipment('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        $this->assertNull($equipment);
    }

    public function test_get_equipment_client_error()
    {
        Http::fake(['*' => Http::response(null, 422)]);

        $service = new InventoryService('inventory');
        $equipment = $service->getEquipment('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        $this->assertNull($equipment);
    }

    public function test_get_equipment_not_found()
    {
        Http::fake(['*' => Http::response(null, 404)]);

        $service = new InventoryService('inventory');
        $equipment = $service->getEquipment('ce283991-b0fb-4ea9-8286-f79157dfd3c1');

        $this->assertNull($equipment);
    }

    public function test_get_equipment()
    {
        $uuid = 'ce283991-b0fb-4ea9-8286-f79157dfd3c1';
        Http::fake(['*' => Http::response(['id' => $uuid])]);

        $service = new InventoryService('inventory');
        $equipment = $service->getEquipment($uuid);

        $this->assertNotNull($equipment);
    }
}
