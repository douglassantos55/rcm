<?php

namespace Tests\Feature;

use App\Models\Period;
use App\Models\RentingValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_list_by_equipment()
    {
        RentingValue::factory()->count(10)->create(['equipment_id' => 'test']);
        RentingValue::factory()->count(20)->create(['equipment_id' => 'something']);

        $response = $this->get(route('renting-values.index', [
            'equipment_id' => 'test'
        ]), ['accept' => 'application/json']);

        $response->assertJsonCount(10);
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
}
