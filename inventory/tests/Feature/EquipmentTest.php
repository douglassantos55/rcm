<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Supplier;
use App\Services\Registry\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Mockery\MockInterface;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->partialMock(Registry::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn(['pricing']);
        });
    }

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

    public function test_create_deleted_supplier()
    {
        $supplier = Supplier::factory()->create(['deleted_at' => now()]);

        $response = $this->post(route('equipment.store'), [
            'description' => 'Tool',
            'unit' => 'mt',
            'supplier_id' => $supplier->id,
            'profit_percentage' => '30',
            'weight' => '20.5',
            'in_stock' => '203',
            'effective_qty' => '223',
            'min_qty' => '351',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => 1050,
                    'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'supplier_id' => 'The selected supplier id is invalid.',
        ]);
    }

    public function test_create()
    {
        Http::fake();

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

    public function test_show()
    {
        Http::fake([
            'pricing/renting-values*' => Http::response([]),
        ]);

        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->get(route('equipment.show', $equipment->id), [
            'accept' => 'application/json'
        ]);

        $response->assertExactJson($equipment->refresh()->toArray());
    }

    public function test_show_not_found()
    {
        $uuid = '1b443f68-4fad-4d01-aacf-6c455ba2bbf4';
        $response = $this->get(route('equipment.show', $uuid));

        $response->assertNotFound();
    }

    public function test_show_soft_deleted()
    {
        $equipment = Equipment::factory()->create([
            'description' => 'Test',
            'deleted_at' => now(),
        ]);

        $response = $this->get(route('equipment.show', $equipment->id), [
            'accept' => 'application/json'
        ]);

        $response->assertNotFound();
    }

    public function test_update()
    {
        Http::fake();

        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->put(route('equipment.update', $equipment->id), [
            'description' => 'Updated',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [],
        ], ['accept' => 'application/json']);

        $response->assertOk();
        $this->assertEquals('Updated', $equipment->refresh()->description);
    }

    public function test_update_validation()
    {
        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->put(route('equipment.update', $equipment->id), [
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

        $response = $this->delete(route('equipment.destroy', $equipment->id), [], [
            'accept' => 'application/json',
        ]);

        $response->assertStatus(204);
        $this->assertSoftDeleted($equipment);
    }

    public function test_delete_not_found()
    {
        $uuid = '0ddb504a-b2b8-4047-86de-0d8862007ccd';

        $response = $this->delete(route('equipment.destroy', $uuid), [], [
            'accept' => 'application/json',
        ]);

        $response->assertNotFound();
    }

    public function test_delete_soft_deleted()
    {
        $equipment = Equipment::factory()->create([
            'description' => 'Test',
            'deleted_at' => now(),
        ]);

        $response = $this->delete(route('equipment.destroy', $equipment->id), [], [
            'accept' => 'application/json',
        ]);

        $response->assertNotFound();
    }

    public function test_list()
    {
        Http::fake([
            'pricing/renting-values*' => Http::response([]),
        ]);

        Equipment::factory()->count(100)->create();
        Equipment::factory()->count(10)->create(['deleted_at' => now()]);

        $response = $this->get(route('equipment.index'), [
            'accept' => 'application/json',
        ]);

        $response->assertJsonCount(50, 'items');
        $this->assertEquals(100, $response['total']);
    }

    public function test_filter_by_description()
    {
        Http::fake([
            'pricing/renting-values*' => Http::response([]),
        ]);

        Equipment::factory()->count(100)->create();
        Equipment::factory()->create(['description' => 'escoramento']);

        $response = $this->get(route('equipment.index', ['description' => 'escora']), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(1, 'items');
        $this->assertEquals(1, $response['total']);
    }

    public function test_filter_by_non_existing_description()
    {
        Http::fake([
            'pricing/renting-values*' => Http::response([]),
        ]);

        Equipment::factory()->count(50)->create();

        $response = $this->get(route('equipment.index', ['description' => 'xyz']), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(0, 'items');
        $this->assertEquals(0, $response['total']);
    }

    public function test_filter_by_supplier()
    {
        Http::fake([
            'pricing/renting-values*' => Http::response([]),
        ]);

        Equipment::factory()->count(50)->create();
        $equipment = Equipment::factory()->forSupplier()->create();

        $response = $this->get(route('equipment.index', [
            'supplier' => $equipment->supplier_id
        ]), ['accept' => 'application/json']);

        $response->assertJsonCount(1, 'items');
        $this->assertEquals(1, $response['total']);
    }

    public function test_filter_by_non_existing_supplier()
    {
        Http::fake([
            'pricing/renting-values*' => Http::response([]),
        ]);

        Equipment::factory()->count(50)->create();

        $response = $this->get(route('equipment.index', ['supplier' => 'xyz']), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(0, 'items');
        $this->assertEquals(0, $response['total']);
    }

    public function test_create_renting_values()
    {
        Http::fake(['pricing/renting-values*' => Http::response([
            '2637fae5-963b-4f5c-8352-c37fbb915d49' => [
                'value' => 1050,
            ],
            '3f63408c-3732-417e-8275-d759e584b84b' => [
                'value' => 1150,
            ],
            '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e' => [
                'value' => 1250,
            ],
        ])]);

        $response = $this->post(route('equipment.store'), [
            'description' => 'With renting values',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => 1050,
                    'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
                [
                    'value' => 1150,
                    'period_id' => '3f63408c-3732-417e-8275-d759e584b84b',
                ],
                [
                    'value' => 1250,
                    'period_id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                ],
            ],
        ], ['accept' => 'application/json']);

        $equipment = $response->json();

        Http::assertSent(function (Request $request, Response $response) use ($equipment) {
            $data = $request->data();

            if ($request->method() === 'GET') {
                return true;
            }

            if (count($data['values']) !== 3) {
                return false;
            }

            foreach ($data['values'] as $value) {
                if (!isset($value['equipment_id']) || $value['equipment_id'] !== $equipment['id']) {
                    return false;
                }
            }

            return $request->url() === 'pricing/renting-values'
                && $request->hasHeader('Authorization')
                && $request->method() === 'POST'
                && $response->successful();
        });
    }

    public function test_server_error_creating_renting_values()
    {
        Http::fake(['pricing/renting-values' => Http::response(null, 500)]);

        $response = $this->post(route('equipment.store'), [
            'description' => 'Ugabuga',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => 1050,
                    'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
                [
                    'value' => 1150,
                    'period_id' => '3f63408c-3732-417e-8275-d759e584b84b',
                ],
                [
                    'value' => 1250,
                    'period_id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                ],
            ],
        ], ['accept' => 'application/json']);

        Http::assertSent(function (Request $request, Response $response) {
            return $request->url() === 'pricing/renting-values'
                && $request->hasHeader('Authorization')
                && $request->method() === 'POST'
                && $response->serverError();
        });

        $response->assertServerError();
        $this->assertNull(Equipment::where('description', 'Ugabuga')->first());
    }

    public function test_request_error_creating_renting_values()
    {
        Http::fake([
            'pricing/renting-values' => Http::response([
                'errors' => [
                    'values.0.value' => 'The value field must be a number.',
                    'values.0.period_id' => 'The period id field is invalid.',
                    'values.1.value' => 'The value field must be a number.',
                    'values.1.period_id' => 'The period id field is invalid.',
                    'values.2.period_id' => 'The period id field is invalid.',
                ],
            ], 422),
        ]);

        $response = $this->post(route('equipment.store'), [
            'description' => 'Ugabuga',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => '30,00',
                    'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
                [
                    'value' => 'text',
                    'period_id' => '3f63408c-3732-417e-8275-d759e584b84b',
                ],
                [
                    'value' => 12.50,
                    'period_id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                ],
            ],
        ], ['accept' => 'application/json']);

        Http::assertSent(function (Request $request, Response $response) {
            return $request->url() === 'pricing/renting-values'
                && $request->hasHeader('Authorization')
                && $request->method() === 'POST'
                && $response->clientError();
        });

        $this->assertNull(Equipment::where('description', 'Ugabuga')->first());

        $response->assertJsonValidationErrors([
            'values.0.value' => 'The value field must be a number.',
            'values.0.period_id' => 'The period id field is invalid.',
            'values.1.value' => 'The value field must be a number.',
            'values.1.period_id' => 'The period id field is invalid.',
            'values.2.period_id' => 'The period id field is invalid.',
        ]);
    }

    public function test_update_renting_values_client_error()
    {
        Http::fake([
            'pricing/renting-values' => Http::response([
                'errors' => [
                    'values.0.value' => 'The value field must be a number.',
                    'values.1.value' => 'The value field must be a number.',
                    'values.2.id' => 'The selected id is invalid.',
                ],
            ], 422),
        ]);

        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->put(route('equipment.update', $equipment->id), [
            'description' => 'Ugabuga',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => '30,00',
                    'id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
                [
                    'value' => 'text',
                    'id' => '3f63408c-3732-417e-8275-d759e584b84b',
                ],
                [
                    'value' => 12.50,
                    'id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                ],
            ],
        ], ['accept' => 'application/json']);

        Http::assertSent(function (Request $request, Response $response) {
            return $request->url() === 'pricing/renting-values'
                && $request->hasHeader('Authorization')
                && $request->method() === 'PUT'
                && $response->clientError();
        });

        $this->assertNotEquals('Ugabuga', $equipment->refresh()->description);

        $response->assertJsonValidationErrors([
            'values.0.value' => 'The value field must be a number.',
            'values.1.value' => 'The value field must be a number.',
            'values.2.id' => 'The selected id is invalid.',
        ]);
    }

    public function test_update_renting_values_server_error()
    {
        Http::fake(['pricing/renting-values' => Http::response(null, 500)]);

        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->put(route('equipment.update', $equipment->id), [
            'description' => 'Ugabuga',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => '30,00',
                    'id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
                [
                    'value' => 'text',
                    'id' => '3f63408c-3732-417e-8275-d759e584b84b',
                ],
                [
                    'value' => 12.50,
                    'id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                ],
            ],
        ], ['accept' => 'application/json']);

        Http::assertSent(function (Request $request, Response $response) {
            return $request->url() === 'pricing/renting-values'
                && $request->hasHeader('Authorization')
                && $request->method() === 'PUT'
                && $response->serverError();
        });

        $response->assertServerError();
        $this->assertNotEquals('Ugabuga', $equipment->refresh()->description);
    }

    public function test_update_renting_values()
    {
        Http::fake(['pricing/renting-values' => Http::response()]);

        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $this->put(route('equipment.update', $equipment->id), [
            'description' => 'Updated',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => '30.00',
                    'id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                    'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
                [
                    'value' => '5.05',
                    'id' => '3f63408c-3732-417e-8275-d759e584b84b',
                    'period_id' => '3f63408c-3732-417e-8275-d759e584b84b',
                ],
                [
                    'value' => 12.50,
                    'id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                    'period_id' => '3f63408c-3732-417e-8275-d759e584b84b',
                ],
            ],
        ], ['accept' => 'application/json']);

        Http::assertSent(function (Request $request, Response $response) {
            $data = $request->data();

            if (!isset($data['values']) || empty($data['values'])) {
                return false;
            }

            return $request->url() === 'pricing/renting-values'
                && $request->hasHeader('Authorization')
                && $request->method() === 'PUT'
                && $response->successful();
        });
    }

    public function test_create_values_exception()
    {
        Http::fake(['pricing/renting-values' => function () {
            throw new HttpClientException('could not resolve host');
        }]);

        $response = $this->post(route('equipment.store'), [
            'description' => 'Exception',
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
            'values' => [
                [
                    'value' => '30.00',
                    'id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                    'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertServerError();
        $response->assertContent('could not reach pricing service');

        Http::assertNothingSent();
        $this->assertNull(Equipment::firstWhere('description', 'Exception'));
    }

    public function test_create_values_multiple_times()
    {
        Http::fake(['pricing/renting-values*' => Http::response([])]);

        $doRequest = function () {
            return $this->post(route('equipment.store'), [
                'description' => 'Max attempts',
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
                'values' => [
                    [
                        'value' => 12.50,
                        'id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                        'period_id' => '3f63408c-3732-417e-8275-d759e584b84b',
                    ],
                ],
            ], ['accept' => 'application/json']);
        };

        for ($i = 0; $i < 5; $i++) {
            $doRequest();
        }

        $response = $doRequest();

        Http::assertSentCount(12);
        $response->assertSuccessful();
    }

    public function test_create_values_max_attempts()
    {
        Http::fake(['pricing/renting-values*' => Http::response(null, 500)]);

        $doRequest = function () {
            return $this->post(route('equipment.store'), [
                'description' => 'Max attempts',
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
                'values' => [
                    [
                        'value' => 12.50,
                        'id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                        'period_id' => '3f63408c-3732-417e-8275-d759e584b84b',
                    ],
                ],
            ], ['accept' => 'application/json']);
        };

        for ($i = 0; $i < 5; $i++) {
            $doRequest();
        }

        $response = $doRequest();

        Http::assertSentCount(5);
        $response->assertServerError();
        $response->assertContent('could not reach pricing service');
    }

    public function test_create_values_reset()
    {
        for ($i = 0; $i < 4; $i++) {
            RateLimiter::hit('pricing');
        }

        Http::fake(['pricing/renting-values*' => Http::response()]);

        $response = $this->post(route('equipment.store'), [
            'description' => 'Max attempts',
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
            'values' => [
                [
                    'value' => 12.50,
                    'id' => '8548880f-a0e3-4d01-b5cd-b8302bdfdf0e',
                    'period_id' => '3f63408c-3732-417e-8275-d759e584b84b',
                ],
            ],
        ], ['accept' => 'application/json']);

        Http::assertSentCount(2);
        $response->assertSuccessful();
        $this->assertEquals(5, RateLimiter::remaining('pricing', 5));
    }

    public function test_update_values_exception()
    {
        Http::fake(['pricing/renting-values' => function () {
            throw new HttpClientException('could not resolve host');
        }]);

        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->put(route('equipment.update', $equipment->id), [
            'description' => 'Updated',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => '30.00',
                    'id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                    'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
            ],
        ], ['accept' => 'application/json']);

        $response->assertServerError();
        $response->assertContent('could not reach pricing service');

        Http::assertNothingSent();
        $this->assertEquals('Test', $equipment->refresh()->description);
    }

    public function test_update_values_multiple_times()
    {
        Http::fake(['pricing/renting-values*' => Http::response()]);

        $doRequest = function (Equipment $equipment) {
            return $this->put(route('equipment.update', $equipment->id), [
                'description' => 'Updated',
                'unit' => 'mt',
                'in_stock' => '203',
                'effective_qty' => '223',
                'purchase_value' => '350.75',
                'unit_value' => '3.33',
                'replace_value' => '550.75',
                'values' => [
                    [
                        'value' => '30.00',
                        'id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                        'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                    ],
                ],
            ], ['accept' => 'application/json']);
        };

        $equipment = Equipment::factory()->create(['description' => 'Test']);

        for ($i = 0; $i < 5; $i++) {
            $doRequest($equipment);
        }

        $response = $doRequest($equipment);

        Http::assertSentCount(12);
        $response->assertSuccessful();
    }

    public function test_update_values_max_attempts()
    {
        Http::fake(['pricing/renting-values' => fn () => throw new \Exception()]);

        $doRequest = function (Equipment $equipment) {
            return $this->put(route('equipment.update', $equipment->id), [
                'description' => 'Updated',
                'unit' => 'mt',
                'in_stock' => '203',
                'effective_qty' => '223',
                'purchase_value' => '350.75',
                'unit_value' => '3.33',
                'replace_value' => '550.75',
                'values' => [
                    [
                        'value' => '30.00',
                        'id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                        'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                    ],
                ],
            ], ['accept' => 'application/json']);
        };

        $equipment = Equipment::factory()->create(['description' => 'Test']);

        for ($i = 0; $i < 5; $i++) {
            $doRequest($equipment);
        }

        $response = $doRequest($equipment);

        Http::assertNothingSent();
        $response->assertServerError();
        $response->assertContent('could not reach pricing service');

        $this->assertEquals('Test', $equipment->refresh()->description);
    }

    public function test_update_values_reset()
    {
        for ($i = 0; $i < 4; $i++) {
            RateLimiter::hit('pricing');
        }

        Http::fake(['pricing/renting-values*' => Http::response()]);
        $equipment = Equipment::factory()->create(['description' => 'Test']);

        $response = $this->put(route('equipment.update', $equipment->id), [
            'description' => 'Updated',
            'unit' => 'mt',
            'in_stock' => '203',
            'effective_qty' => '223',
            'purchase_value' => '350.75',
            'unit_value' => '3.33',
            'replace_value' => '550.75',
            'values' => [
                [
                    'value' => '30.00',
                    'id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                    'period_id' => '2637fae5-963b-4f5c-8352-c37fbb915d49',
                ],
            ],
        ], ['accept' => 'application/json']);

        Http::assertSentCount(2);
        $response->assertSuccessful();
        $this->assertEquals(5, RateLimiter::remaining('pricing', 5));
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
