<?php

namespace Tests\Feature;

use App\Models\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_validation()
    {
        $response = $this->post(route('periods.store'), [
            'name' => '',
            'qty_days' => 'hundred',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'name' => 'The name field is required.',
            'qty_days' => 'The qty days field must be an integer.',
        ]);
    }

    public function test_create()
    {
        $response = $this->post(route('periods.store'), [
            'name' => 'Daily',
            'qty_days' => '1',
        ], ['accept' => 'application/json']);

        $period = Period::where('name', 'Daily')->first();
        $this->assertModelExists($period);

        $response->assertCreated();
        $response->assertExactJson($period->toArray());
    }

    public function test_update_validation()
    {
        $period = Period::factory()->create(['name' => 'Daily', 'qty_days' => 1]);

        $response = $this->put(route('periods.update', $period->id), [
            'name' => '',
            'qty_days' => 'hundred',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'name' => 'The name field is required.',
            'qty_days' => 'The qty days field must be an integer.',
        ]);
    }

    public function test_update()
    {
        $period = Period::factory()->create(['name' => 'Daily', 'qty_days' => 1]);

        $response = $this->put(route('periods.update', $period->id), [
            'name' => 'Weekly',
            'qty_days' => 7,
        ], ['accept' => 'application/json']);

        $period->refresh();
        $response->assertExactJson($period->toArray());
    }

    public function test_delete_non_existent()
    {
        $uuid = '91888150-eeb1-4586-a1f1-dc50fc3d5c97';

        $response = $this->delete(route('periods.destroy', $uuid), [], [
            'accept' => 'application/json',
        ]);

        $response->assertNotFound();
    }

    public function test_delete_soft_deleted()
    {
        $period = Period::factory()->create(['deleted_at' => now()]);

        $response = $this->delete(route('periods.destroy', $period->id), [], [
            'accept' => 'application/json',
        ]);

        $response->assertNotFound();
    }

    public function test_delete()
    {
        $period = Period::factory()->create();

        $response = $this->delete(route('periods.destroy', $period->id), [], [
            'accept' => 'application/json',
        ]);

        $response->assertNoContent();
        $this->assertSoftDeleted($period);
    }
}
