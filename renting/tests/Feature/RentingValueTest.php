<?php

namespace Tests\Feature;

use App\Models\Period;
use App\Models\RentingValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RentingValueTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_validation()
    {
        $response = $this->post(route('renting-values.store'), [
            'values' => [
                [
                    'value' => '230,00',
                    'period_id' => 'fb5e627a-af24-46f9-8f20-955b79c17d56',
                    'equipment_id' => ' ',
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'values.0.value' => 'The value field must be a number.',
            'values.0.period_id' => 'The selected period is invalid.',
            'values.0.equipment_id' => 'The equipment field is required.',
        ]);
    }

    public function test_create_duplicated_period_and_equipment()
    {
        Http::fake(['*' => Http::response(['id' => 'aoeu'])]);

        $period = Period::factory()->create(['name' => 'Daily']);

        $response = $this->post(route('renting-values.store'), [
            'values' => [
                [
                    'value' => '230.00',
                    'period_id' => $period->id,
                    'equipment_id' => '8dde394d-bd3e-4a4e-8483-4ff3bd8e8f49',
                ],
                [
                    'value' => 30.00,
                    'period_id' => $period->id,
                    'equipment_id' => '8dde394d-bd3e-4a4e-8483-4ff3bd8e8f49',
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertServerError();
    }

    public function test_create_duplicated_period()
    {
        Http::fake(['*' => Http::response(['id' => 'aoeu'])]);

        $period = Period::factory()->create(['name' => 'Daily']);

        $response = $this->post(route('renting-values.store'), [
            'values' => [
                [
                    'value' => '230.00',
                    'period_id' => $period->id,
                    'equipment_id' => '8dde394d-bd3e-4a4e-8483-4ff3bd8e8f49',
                ],
                [
                    'value' => 30.00,
                    'period_id' => $period->id,
                    'equipment_id' => '11107582-2f38-4d12-a347-9b435b51686e',
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertCreated();
        $this->assertDatabaseCount(RentingValue::class, 2);
    }

    public function test_create_soft_deleted_period()
    {
        $period = Period::factory()->create(['deleted_at' => now()]);

        $response = $this->post(route('renting-values.store'), [
            'values' => [
                [
                    'value' => '230.00',
                    'period_id' => $period->id,
                    'equipment_id' => '8dde394d-bd3e-4a4e-8483-4ff3bd8e8f49',
                ],
                [
                    'value' => 30.00,
                    'period_id' => $period->id,
                    'equipment_id' => '11107582-2f38-4d12-a347-9b435b51686e',
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'values.0.period_id' => 'The selected period is invalid.',
            'values.1.period_id' => 'The selected period is invalid.',
        ]);
    }

    public function test_list_by_equipment()
    {
        $periods = Period::factory()->createMany([
            ['name' => 'daily', 'qty_days' => 1],
            ['name' => 'weekly', 'qty_days' => 7],
            ['name' => 'monthly', 'qty_days' => 30],
        ]);

        $values = RentingValue::factory()->createMany([
            [
                'value' => 0.5,
                'period_id' => $periods[0]->id,
                'equipment_id' => 'test',
            ],
            [
                'value' => 1.5,
                'period_id' => $periods[1]->id,
                'equipment_id' => 'test',
            ],
            [
                'value' => 2.5,
                'period_id' => $periods[2]->id,
                'equipment_id' => 'test',
            ],
            [
                'value' => 0.5,
                'period_id' => $periods[0]->id,
                'equipment_id' => 'other',
            ],
            [
                'value' => 1.5,
                'period_id' => $periods[1]->id,
                'equipment_id' => 'other',
            ],
            [
                'value' => 2.5,
                'period_id' => $periods[2]->id,
                'equipment_id' => 'other',
            ],
        ]);

        $response = $this->get(route('renting-values.index', [
            'equipment_id' => 'test'
        ]), ['accept' => 'application/json']);

        $response->assertExactJson([
            $periods[0]->id => $values[0]->toArray(),
            $periods[1]->id => $values[1]->toArray(),
            $periods[2]->id => $values[2]->toArray(),
        ]);
    }

    public function test_list_no_equipment()
    {
        RentingValue::factory()->count(30)->create();

        $response = $this->get(route('renting-values.index'), [
            'accept' => 'application/json',
        ]);

        $response->assertBadRequest();
        $response->assertContent('equipment_id required');
    }

    public function test_update_validation()
    {
        $response = $this->put(route('renting-values.update'), [
            'values' => [
                [
                    'id' => 'f32b3dd1-d17c-4cdc-846c-0a08e53d6bc6',
                    'value' => '230,00',
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'values.0.id' => 'The selected id is invalid.',
            'values.0.value' => 'The value field must be a number.',
        ]);
    }

    public function test_update_non_existent()
    {
        $period = Period::factory()->create([
            'name' => 'Daily',
            'qty_days' => 1
        ]);

        $value = RentingValue::factory()->create([
            'value' => 0.75,
            'period_id' => $period->id,
            'equipment_id' => 'first',
        ]);

        $response = $this->put(route('renting-values.update'), [
            'values' => [
                [
                    'id' => $value->id,
                    'value' => '2.00',
                ],
                [
                    'id' => '73eb0be5-131a-435e-a91a-36d1e623009c',
                    'value' => 3.00,
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'values.1.id' => 'The selected id is invalid.',
        ]);

        $this->assertEquals(0.75, $value->refresh()->value);
    }

    public function test_update()
    {
        $daily  = Period::factory()->create(['name' => 'Daily', 'qty_days' => 1]);
        $weekly  = Period::factory()->create(['name' => 'Weekly', 'qty_days' => 7]);

        $values = RentingValue::factory()->createMany([
            ['value' => 0.75, 'period_id' => $daily->id, 'equipment_id' => 'first'],
            ['value' => 0.85, 'period_id' => $weekly->id, 'equipment_id' => 'first'],
        ]);

        $response = $this->put(route('renting-values.update'), [
            'values' => [
                [
                    'value' => '2.00',
                    'id' => $values[0]->id,
                ],
                [
                    'value' => 3.00,
                    'id' => $values[1]->id,
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertSuccessful();

        $this->assertEquals(2, $values[0]->refresh()->value);
        $this->assertEquals(3, $values[1]->refresh()->value);
    }
}
