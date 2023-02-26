<?php

namespace Tests\Feature;

use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation()
    {
        $response = $this->withToken($this->validToken)->post(route('equipment.store'), [
            'description' => '  ',
            'unit' => 'kg',
            'supplier_id' => 'b396a772-242c-4974-9493-6418fa843fd1',
            'profit_percentage' => '-1',
            'weight' => '20,5',
            'in_stock' => '20.3',
            'effective_qty' => '22.3',
            'min_qty' => '35.1',
            'purchase_value' => '',
            'unit_value' => '2509,66',
            'replace_value' => '1_000_000',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'description' => 'The description field is required.',
            'unit' => 'The selected unit is invalid.',
            'supplier_id' => 'The selected supplier id is invalid.',
            'profit_percentage' => 'The profit percentage field must be at least 0.',
            'weight' => 'The weight field must be a number.',
            'in_stock' => 'The in stock field must be an integer.',
            'effective_qty' => 'The effective qty field must be an integer.',
            'min_qty' => 'The min qty field must be an integer.',
            'purchase_value' => 'The purchase value field is required.',
            'unit_value' => 'The unit value field must be a number.',
            'replace_value' => 'The replace value field must be a number.',
        ]);
    }

    public function test_create()
    {
        $response = $this->withToken($this->validToken)->post(route('equipment.store'), [
            'description' => 'Tool',
            'unit' => 'mt',
            'supplier_id' => null,
            'profit_percentage' => '30',
            'weight' => '20.5',
            'in_stock' => '203',
            'effective_qty' => '223',
            'min_qty' => '351',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
        ], ['accept' => 'application/json']);

        $response->assertStatus(201);
        $this->assertModelExists(Equipment::where('description', 'Tool')->first());
    }

    public function test_show()
    {
        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->withToken($this->validToken)->get(route('equipment.show', $equipment->id), [
            'accept' => 'application/json'
        ]);

        $response->assertExactJson($equipment->refresh()->toArray());
    }

    public function test_show_not_found()
    {
        $uuid = '1b443f68-4fad-4d01-aacf-6c455ba2bbf4';
        $response = $this->withToken($this->validToken)->get(route('equipment.show', $uuid));

        $response->assertNotFound();
    }

    public function test_show_soft_deleted()
    {
        $equipment = Equipment::factory()->create([
            'description' => 'Test',
            'deleted_at' => now(),
        ]);

        $response = $this->withToken($this->validToken)->get(route('equipment.show', $equipment->id), [
            'accept' => 'application/json'
        ]);

        $response->assertNotFound();
    }

    public function test_update()
    {
        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->withToken($this->validToken)->put(route('equipment.update', $equipment->id), [
            'description' => 'Updated',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
        ], ['accept' => 'application/json']);

        $response->assertOk();
        $this->assertEquals('Updated', $equipment->refresh()->description);
    }

    public function test_update_validation()
    {
        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->withToken($this->validToken)->put(route('equipment.update', $equipment->id), [
            'description' => '  ',
            'unit' => 'kg',
            'supplier_id' => 'b396a772-242c-4974-9493-6418fa843fd1',
            'profit_percentage' => '1001',
            'weight' => '20,5',
            'in_stock' => '20.3',
            'effective_qty' => '22.3',
            'min_qty' => '35.1',
            'purchase_value' => '',
            'unit_value' => '2509,66',
            'replace_value' => '1_000_000',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'description' => 'The description field is required.',
            'unit' => 'The selected unit is invalid.',
            'supplier_id' => 'The selected supplier id is invalid.',
            'profit_percentage' => 'The profit percentage field must not be greater than 100.',
            'weight' => 'The weight field must be a number.',
            'in_stock' => 'The in stock field must be an integer.',
            'effective_qty' => 'The effective qty field must be an integer.',
            'min_qty' => 'The min qty field must be an integer.',
            'purchase_value' => 'The purchase value field is required.',
            'unit_value' => 'The unit value field must be a number.',
            'replace_value' => 'The replace value field must be a number.',
        ]);
    }

    public function test_soft_delete()
    {
        $equipment = Equipment::factory()->create(['description' => 'Test']);
        $response = $this->withToken($this->validToken)->delete(route('equipment.destroy', $equipment->id));

        $response->assertStatus(204);
        $this->assertSoftDeleted($equipment);
    }

    public function test_delete_not_found()
    {
        $uuid = '0ddb504a-b2b8-4047-86de-0d8862007ccd';
        $response = $this->withToken($this->validToken)->delete(route('equipment.destroy', $uuid));
        $response->assertNotFound();
    }

    public function test_delete_soft_deleted()
    {
        $equipment = Equipment::factory()->create([
            'description' => 'Test',
            'deleted_at' => now(),
        ]);

        $response = $this->withToken($this->validToken)->delete(route('equipment.destroy', $equipment->id));
        $response->assertNotFound();
    }

    public function test_list()
    {
        Equipment::factory()->count(10)->create();
        Equipment::factory()->count(10)->create(['deleted_at' => now()]);

        $response = $this->withToken($this->validToken)->get(route('equipment.index'), [
            'accept' => 'application/json',
        ]);

        $response->assertJsonCount(10);
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_create_unauthorized(string $token)
    {
        $response = $this->withToken($token)->post(route('equipment.store'), [
            'description' => 'Tool',
            'unit' => 'mt',
            'supplier_id' => null,
            'profit_percentage' => '30',
            'weight' => '20.5',
            'in_stock' => '203',
            'effective_qty' => '223',
            'min_qty' => '351',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
        ], ['accept' => 'application/json']);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_show_unauthorized(string $token)
    {
        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->withToken($token)->get(route('equipment.show', $equipment->id), [
            'accept' => 'application/json'
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_update_unauthorized(string $token)
    {
        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->withToken($token)->put(route('equipment.update', $equipment->id), [
            'description' => 'Updated',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
        ], ['accept' => 'application/json']);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_soft_delete_unauthorized(string $token)
    {
        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $route = route('equipment.destroy', $equipment->id);
        $response = $this->withToken($token)->delete($route, [], ['accept' => 'application/json']);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_list_unauthorized(string $token)
    {
        $response = $this->withToken($token)->get(route('equipment.index'), [
            'accept' => 'application/json'
        ]);

        $response->assertUnauthorized();
    }
}
