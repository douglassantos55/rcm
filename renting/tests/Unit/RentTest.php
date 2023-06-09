<?php

namespace Tests\Unit;

use App\Messenger\Messenger;
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
            $mock->shouldReceive('get')->with('pricing')->andReturn(['pricing']);
        });

        $this->partialMock(Messenger::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')->andReturn();
        });
    }

    public function test_bulk_from_individuals_cached()
    {
        $rent = Rent::factory()->create();

        Http::fake([
            'inventory/equipment?uuids*' => Http::response([
                [
                    'id' => '3272',
                    'unit_value' => '250',
                    'values' => [
                        $rent->period_id => ['value' => 0.5],
                    ],
                ],
                [
                    'id' => '3030',
                    'unit_value' => '150',
                    'values' => [
                        $rent->period_id => ['value' => 0.5],
                    ],
                ],
            ]),
            'inventory/equipment/3272' => Http::response([
                'id' => '3272',
                'unit_value' => '250',
                'values' => [
                    $rent->period_id => ['value' => 0.5],
                ],
            ]),
            'inventory/equipment/3030' => Http::response([
                'id' => '3030',
                'unit_value' => '150',
                'values' => [
                    $rent->period_id => ['value' => 0.5],
                ],
            ]),
            'payment/*' => Http::response(['increment' => 15]),
        ]);

        $rent->items()->createMany([
            ['equipment_id' => '3272', 'qty' => 10],
            ['equipment_id' => '3030', 'qty' => 1],
        ]);

        // Send bulk request
        $this->get(route('rents.show', $rent->id));

        // Two requests for inserting items, then use cache
        // Three requests for rent's payment method, type and condition
        Http::assertSentCount(5);
    }

    public function test_get_individuals()
    {
        $rent = Rent::factory()->create();

        Http::fake([
            'inventory/equipment/3272' => Http::response([
                'id' => '3272',
                'unit_value' => '250',
                'values' => [
                    $rent->period_id => ['value' => 0.3],
                ],
            ]),
            'inventory/equipment/3030' => Http::response([
                'id' => '3030',
                'unit_value' => '150',
                'values' => [
                    $rent->period_id => ['value' => 0.75],
                ],
            ]),
            'inventory/equipment/4030' => Http::response([
                'id' => '4030',
                'unit_value' => '350',
                'values' => [
                    $rent->period_id => ['value' => 1.75],
                ],
            ]),
            'payment/*' => Http::response(['increment' => 15]),
        ]);

        $rent->items()->createMany([
            ['equipment_id' => '3272', 'qty' => 10],
            ['equipment_id' => '3030', 'qty' => 1],
            ['equipment_id' => '4030', 'qty' => 1],
        ]);

        // Individual requests
        json_encode($rent->items);

        // Three requests for inserting items, then use cache
        // One request for payment condition
        Http::assertSentCount(4);
    }

    public function test_get_bulk()
    {
        $rent = Rent::factory()->create();

        Http::fake([
            'inventory/equipment/3272' => Http::response([
                'id' => '3272',
                'unit_value' => '250',
                'values' => [
                    $rent->period_id => ['value' => 0.5],
                ],
            ]),
            'inventory/equipment/3030' => Http::response([
                'id' => '3030',
                'unit_value' => '150',
                'values' => [
                    $rent->period_id => ['value' => 0.5],
                ],
            ]),
            'inventory/equipment/4030' => Http::response([
                'id' => '4030',
                'unit_value' => '350',
                'values' => [
                    $rent->period_id => ['value' => 0.5],
                ],
            ]),
            'inventory/equipment?uuid*' => Http::response([
                [
                    'id' => '3272',
                    'unit_value' => '250',
                    'values' => [
                        $rent->period_id => ['value' => 0.3],
                    ],
                ],
                [
                    'id' => '3030',
                    'unit_value' => '150',
                    'values' => [
                        $rent->period_id => ['value' => 0.75],
                    ],
                ],
                [
                    'id' => '4030',
                    'unit_value' => '350',
                    'values' => [
                        $rent->period_id => ['value' => 1.75],
                    ],
                ],
            ]),
            'payment/*' => Http::response(['increment' => 0]),
        ]);

        $rent->items()->createMany([
            ['equipment_id' => '3272', 'qty' => 10],
            ['equipment_id' => '3030', 'qty' => 1],
            ['equipment_id' => '4030', 'qty' => 1],
        ]);

        // Clear cache to force a request
        $this->travel(6)->seconds();

        // Trigger retrieved event on model
        $rent->refresh();

        // Three requests for inserting items
        // One for retrieving items in bulk
        // One for retrieving payment condition
        Http::assertSentCount(5);
    }

    public function test_single_from_bulk_cached()
    {
        $rent = Rent::factory()->create();

        Http::fake([
            'inventory/equipment/3272' => Http::response([
                'id' => '3272',
                'unit_value' => '250',
                'values' => [
                    $rent->period_id => ['value' => 0.5],
                ],
            ]),
            'inventory/equipment/3030' => Http::response([
                'id' => '3030',
                'unit_value' => '150',
                'values' => [
                    $rent->period_id => ['value' => 0.5],
                ],
            ]),
            'inventory/equipment/4030' => Http::response([
                'id' => '4030',
                'unit_value' => '350',
                'values' => [
                    $rent->period_id => ['value' => 0.5],
                ],
            ]),
            'inventory/equipment?uuid*' => Http::response([
                [
                    'id' => '3272',
                    'unit_value' => '250',
                    'values' => [
                        $rent->period_id => ['value' => 0.3],
                    ],
                ],
                [
                    'id' => '3030',
                    'unit_value' => '150',
                    'values' => [
                        $rent->period_id => ['value' => 0.75],
                    ],
                ],
                [
                    'id' => '4030',
                    'unit_value' => '350',
                    'values' => [
                        $rent->period_id => ['value' => 1.75],
                    ],
                ],
            ]),
            'payment/*' => Http::response(['increment' => 0]),
        ]);

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

        // Three requests for inserting items,
        // One for retrieving items in bulk
        // One for retrieving payment condition
        Http::assertSentCount(5);
    }
}
