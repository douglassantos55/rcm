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
        $response = $this->post(route('equipment.store'), [
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
        $response = $this->post(route('equipment.store'), [
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
}
