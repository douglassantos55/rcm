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
}
