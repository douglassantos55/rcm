<?php

namespace Tests\Unit;

use App\Models\Item;
use App\Services\Registry\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->partialMock(Registry::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn(['inventory']);
        });
    }

    public function test_appends_equipment()
    {
        $equipment = ['unit_value' => '15.55', 'rent_value' => 0.3];
        Http::fake(['*' => Http::response($equipment)]);

        $item = Item::factory()->create();
        $this->assertEquals($equipment, $item->equipment);
    }

    public function test_appends_equipment_not_found()
    {
        Http::fake(['*' => Http::response(null, 404)]);

        $item = Item::factory()->create();
        $this->assertEquals(null, $item->equipment);
    }

    public function test_appends_equipment_server_error()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $item = Item::factory()->create();
        $this->assertEquals(null, $item->equipment);
    }
}
