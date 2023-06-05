<?php

namespace Tests\Unit;

use App\Models\Rent;
use App\Services\Registry\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class RentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->partialMock(Registry::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->with('inventory')->andReturn(['inventory']);
            $mock->shouldReceive('get')->with('payment')->andReturn(['payment']);
        });
    }

    public function test_bulk_from_individuals_cached()
    {
        Http::fake([
            'inventory/equipment?uuids*' => Http::response([
                ['id' => '3272', 'rent_value' => '0.3', 'unit_value' => '250'],
                ['id' => '3030', 'rent_value' => '0.75', 'unit_value' => '150'],
            ]),
            'inventory/equipment/3272' => Http::response([
                'id' => '3272', 'rent_value' => '0.3', 'unit_value' => '250'
            ]),
            'inventory/equipment/3030' => Http::response([
                'id' => '3030', 'rent_value' => '0.75', 'unit_value' => '150'
            ]),
        ]);

        $rent = Rent::factory()->create();

        $rent->items()->createMany([
            ['equipment_id' => '3272', 'qty' => 10],
            ['equipment_id' => '3030', 'qty' => 1],
        ]);

        // Send bulk request
        $this->get(route('rents.show', $rent->id));

        // Two requests for inserting items, then use cache
        Http::assertSentCount(2);
    }

    public function test_get_individuals()
    {
        Http::fake([
            'inventory/equipment/3272' => Http::response([
                'id' => '3272', 'rent_value' => '0.3', 'unit_value' => '250'
            ]),
            'inventory/equipment/3030' => Http::response([
                'id' => '3030', 'rent_value' => '0.75', 'unit_value' => '150'
            ]),
            'inventory/equipment/4030' => Http::response([
                'id' => '4030', 'rent_value' => '1.75', 'unit_value' => '350'
            ]),
        ]);

        $rent = Rent::factory()->create();

        $rent->items()->createMany([
            ['equipment_id' => '3272', 'qty' => 10],
            ['equipment_id' => '3030', 'qty' => 1],
            ['equipment_id' => '4030', 'qty' => 1],
        ]);

        // Individual requests
        json_encode($rent->items);

        // Three requests for inserting items, then use cache
        Http::assertSentCount(3);
    }

    public function test_get_bulk()
    {
        Http::fake([
            'inventory/equipment/3272' => Http::response([
                'id' => '3272', 'rent_value' => '0.3', 'unit_value' => '250'
            ]),
            'inventory/equipment/3030' => Http::response([
                'id' => '3030', 'rent_value' => '0.75', 'unit_value' => '150'
            ]),
            'inventory/equipment/4030' => Http::response([
                'id' => '4030', 'rent_value' => '1.75', 'unit_value' => '350'
            ]),
            'inventory/equipment?uuid*' => Http::response([
                ['id' => '3272', 'rent_value' => '0.3', 'unit_value' => '250'],
                ['id' => '3030', 'rent_value' => '0.75', 'unit_value' => '150'],
                ['id' => '4030', 'rent_value' => '1.75', 'unit_value' => '350'],
            ]),
        ]);

        $rent = Rent::factory()->create();

        $rent->items()->createMany([
            ['equipment_id' => '3272', 'qty' => 10],
            ['equipment_id' => '3030', 'qty' => 1],
            ['equipment_id' => '4030', 'qty' => 1],
        ]);

        // Clear cache to force a request
        $this->travel(6)->seconds();

        // Trigger retrieved event on model
        $rent->refresh();

        // Three requests for inserting items, then one more for retrieving items
        Http::assertSentCount(4);
    }

    public function test_single_from_bulk_cached()
    {
        Http::fake([
            'inventory/equipment/3272' => Http::response([
                'id' => '3272', 'rent_value' => '0.3', 'unit_value' => '250'
            ]),
            'inventory/equipment/3030' => Http::response([
                'id' => '3030', 'rent_value' => '0.75', 'unit_value' => '150'
            ]),
            'inventory/equipment/4030' => Http::response([
                'id' => '4030', 'rent_value' => '1.75', 'unit_value' => '350'
            ]),
            'inventory/equipment?uuid*' => Http::response([
                ['id' => '3272', 'rent_value' => '0.3', 'unit_value' => '250'],
                ['id' => '3030', 'rent_value' => '0.75', 'unit_value' => '150'],
                ['id' => '4030', 'rent_value' => '1.75', 'unit_value' => '350'],
            ]),
        ]);

        $rent = Rent::factory()->create();

        $rent->items()->createMany([
            ['equipment_id' => '3272', 'qty' => 10],
            ['equipment_id' => '3030', 'qty' => 1],
            ['equipment_id' => '4030', 'qty' => 1],
        ]);

        // Clear cache to force a request
        $this->travel(6)->seconds();

        // Trigger retrieved event on model
        $rent->refresh();

        json_encode($rent->items);

        // Three requests for inserting items, then one more for retrieving items
        Http::assertSentCount(4);
    }
}
