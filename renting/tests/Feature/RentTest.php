<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Item;
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
    }

    public function test_create_validation()
    {
        Http::fake([
            'payment/*' => Http::response(null, 404),
            'inventory/*' => Http::response(null, 404),
            'pricing/*' => Http::response(null, 404),
        ]);

        $response = $this->post(route('rents.store'), [
            'start_date' => '2020-20-10',
            'end_date' => '2020-20-20',
            'qty_days' => '9.55',
            'discount' => 'hundred',
            'paid_value' => 'million',
            'delivery_value' => 'some bucks',
            'bill' => 'dollar',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => 'nice dude',
            'observations' => 'nothing to add',
            'transporter' => '',
            'customer_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'period_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => '0.5', 'equipment_id' => 'aoeu'],
                ['qty' => '5', 'equipment_id' => 'snth'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'start_date' => 'The start date field must be a valid date.',
            'end_date' => 'The end date field must be a valid date.',
            'qty_days' => 'The qty days field must be an integer.',
            'discount' => 'The discount field must be a number.',
            'paid_value' => 'The paid value field must be a number.',
            'delivery_value' => 'The delivery value field must be a number.',
            'bill' => 'The bill field must be a number.',
            'customer_id' => 'The selected customer id is invalid.',
            'period_id' => 'The selected period id is invalid.',
            'payment_type_id' => 'The selected payment type id is invalid.',
            'payment_method_id' => 'The selected payment method id is invalid.',
            'payment_condition_id' => 'The selected payment condition id is invalid.',
            'items.0.qty' => 'The items.0.qty field must be an integer.',
            'items.0.equipment_id' => 'The selected items.0.equipment_id is invalid.',
            'items.1.equipment_id' => 'The selected items.1.equipment_id is invalid.',
        ]);
    }

    public function test_create_deleted_customer()
    {
        Http::fake([
            'payment/*' => Http::response(['foo' => 'bar']),
            'pricing/*' => Http::response(['foo' => 'bar']),
        ]);

        $customer = Customer::factory()->create(['deleted_at' => now()]);

        $response = $this->post(route('rents.store'), [
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-20',
            'qty_days' => '10',
            'discount' => '',
            'paid_value' => '',
            'delivery_value' => '',
            'bill' => '',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => '',
            'observations' => '',
            'transporter' => '',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'customer_id' => 'The selected customer id is invalid.',
        ]);
    }

    public function test_create_deleted_period()
    {
        Http::fake([
            'payment/*' => Http::response(['foo' => 'bar']),
            'pricing/*' => Http::response(null, 404),
        ]);

        $customer = Customer::factory()->create();

        $response = $this->post(route('rents.store'), [
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-20',
            'qty_days' => '10',
            'discount' => '',
            'paid_value' => '',
            'delivery_value' => '',
            'bill' => '',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => '',
            'observations' => '',
            'transporter' => '',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'period_id' => 'The selected period id is invalid.',
        ]);
    }

    public function test_create()
    {
        Http::fake([
            'payment/*' => Http::response(['foo' => 'bar']),
            'inventory/*' => Http::response(['rent_value' => '0.35', 'unit_value' => 1000]),
            'pricing/*' => Http::response(['foo' => 'bar']),
        ]);

        $customer = Customer::factory()->create();

        $response = $this->post(route('rents.store'), [
            'start_date' => '2020-10-10 22:52:30',
            'end_date' => '2020-10-20 22:52:30',
            'qty_days' => '10',
            'discount' => '',
            'paid_value' => '',
            'delivery_value' => '',
            'bill' => '',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => '',
            'observations' => 'Testing creation',
            'transporter' => '',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => '5', 'equipment_id' => '73686c69-2307-48bb-bb5f-c4acbb71aa58'],
                ['qty' => '5', 'equipment_id' => '3ac65dee-367e-48ec-8486-08d6e729cca4'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertCreated();
        $rent = Rent::first();

        $this->assertModelExists($rent);
        $this->assertCount(2, Item::all());
        $this->assertEquals('Testing creation', $rent->observations);
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_create_invalid_token(string $token)
    {
        $customer = Customer::factory()->create();

        $response = $this->withToken($token)->post(route('rents.store'), [
            'start_date' => '2020-10-10 22:52:30',
            'end_date' => '2020-10-20 22:52:30',
            'qty_days' => '10',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => '5', 'equipment_id' => '73686c69-2307-48bb-bb5f-c4acbb71aa58'],
                ['qty' => '5', 'equipment_id' => '3ac65dee-367e-48ec-8486-08d6e729cca4'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertUnauthorized();
    }

    public function test_update_validation()
    {
        Http::fake([
            'payment/*' => Http::response(null, 404),
            'inventory/*' => Http::response(null, 404),
            'pricing/*' => Http::response(null, 404),
        ]);

        $rent = Rent::factory()->create();

        $response = $this->put(route('rents.update', $rent->id), [
            'start_date' => '2020-20-10',
            'end_date' => '2020-20-20',
            'qty_days' => '9.55',
            'discount' => 'hundred',
            'paid_value' => 'million',
            'delivery_value' => 'some bucks',
            'bill' => 'dollar',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => 'nice dude',
            'observations' => 'nothing to add',
            'transporter' => '',
            'customer_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'period_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => 'five', 'equipment_id' => '73686c69-2307-48bb-bb5f-c4acbb71aa58'],
                ['qty' => '5', 'equipment_id' => '3ac65dee-367e-48ec-8486-08d6e729cca4'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'start_date' => 'The start date field must be a valid date.',
            'end_date' => 'The end date field must be a valid date.',
            'qty_days' => 'The qty days field must be an integer.',
            'discount' => 'The discount field must be a number.',
            'paid_value' => 'The paid value field must be a number.',
            'delivery_value' => 'The delivery value field must be a number.',
            'bill' => 'The bill field must be a number.',
            'customer_id' => 'The selected customer id is invalid.',
            'period_id' => 'The selected period id is invalid.',
            'payment_type_id' => 'The selected payment type id is invalid.',
            'payment_method_id' => 'The selected payment method id is invalid.',
            'payment_condition_id' => 'The selected payment condition id is invalid.',
            'items.0.qty' => 'The items.0.qty field must be an integer.',
            'items.0.equipment_id' => 'The selected items.0.equipment_id is invalid.',
            'items.1.equipment_id' => 'The selected items.1.equipment_id is invalid.',
        ]);
    }

    public function test_update_deleted_customer()
    {
        Http::fake([
            'payment/*' => Http::response(['foo' => 'baz']),
            'pricing/*' => Http::response(['foo' => 'baz']),
            'inventory/*' => Http::response([
                'rent_value' => '0.3', 'unit_value' => '250'
            ]),
        ]);

        $rent = Rent::factory()->create();
        $customer = Customer::factory()->create(['deleted_at' => now()]);

        $response = $this->put(route('rents.update', $rent->id), [
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-20',
            'qty_days' => '10',
            'discount' => '',
            'paid_value' => '',
            'delivery_value' => '',
            'bill' => '',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => '',
            'observations' => '',
            'transporter' => '',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => '5', 'equipment_id' => '73686c69-2307-48bb-bb5f-c4acbb71aa58'],
                ['qty' => '5', 'equipment_id' => '3ac65dee-367e-48ec-8486-08d6e729cca4'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'customer_id' => 'The selected customer id is invalid.',
        ]);
    }

    public function test_update_deleted_period()
    {
        Http::fake([
            'payment/*' => Http::response(['foo' => 'baz']),
            'pricing/*' => Http::response(null, 404),
            'inventory/*' => Http::response([
                'rent_value' => '0.3', 'unit_value' => '250'
            ]),
        ]);

        $rent = Rent::factory()->create();
        $customer = Customer::factory()->create();

        $response = $this->put(route('rents.update', $rent->id), [
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-20',
            'qty_days' => '10',
            'discount' => '',
            'paid_value' => '',
            'delivery_value' => '',
            'bill' => '',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => '',
            'observations' => '',
            'transporter' => '',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => '5', 'equipment_id' => '73686c69-2307-48bb-bb5f-c4acbb71aa58'],
                ['qty' => '5', 'equipment_id' => '3ac65dee-367e-48ec-8486-08d6e729cca4'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'period_id' => 'The selected period id is invalid.',
        ]);
    }

    public function test_update_non_existent()
    {
        Http::fake([
            'payment/*' => Http::response(['foo' => 'baz']),
            'pricing/*' => Http::response(['foo' => 'baz']),
            'inventory/*' => Http::response([
                'rent_value' => '0.3', 'unit_value' => '250'
            ]),
        ]);

        $customer = Customer::factory()->create();
        $uuid = '62578f05-85f2-442b-8412-df47d188e01b';

        $response = $this->put(route('rents.update', $uuid), [
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-20',
            'qty_days' => '10',
            'discount' => '',
            'paid_value' => '',
            'delivery_value' => '',
            'bill' => '',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => '',
            'observations' => '',
            'transporter' => '',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => '5', 'equipment_id' => '73686c69-2307-48bb-bb5f-c4acbb71aa58'],
                ['qty' => '5', 'equipment_id' => '3ac65dee-367e-48ec-8486-08d6e729cca4'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertNotFound();
    }

    public function test_update_soft_deleted_rent()
    {
        Http::fake([
            'payment/*' => Http::response(['foo' => 'baz']),
            'pricing/*' => Http::response(['foo' => 'baz']),
            'inventory/*' => Http::response([
                'rent_value' => '0.3', 'unit_value' => '250'
            ]),
        ]);

        $rent = Rent::factory()->create(['deleted_at' => now()]);
        $customer = Customer::factory()->create();

        $response = $this->put(route('rents.update', $rent->id), [
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-20',
            'qty_days' => '10',
            'discount' => '',
            'paid_value' => '',
            'delivery_value' => '',
            'bill' => '',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => '',
            'observations' => '',
            'transporter' => '',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => '5', 'equipment_id' => '73686c69-2307-48bb-bb5f-c4acbb71aa58'],
                ['qty' => '5', 'equipment_id' => '3ac65dee-367e-48ec-8486-08d6e729cca4'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertNotFound();
    }

    public function test_update()
    {
        Http::fake([
            'payment/*' => Http::response(['foo' => 'baz']),
            'pricing/*' => Http::response(['foo' => 'baz']),
            'inventory/equipment/3272' => Http::response([
                'rent_value' => '0.3', 'unit_value' => '250'
            ]),
            'inventory/equipment/3030' => Http::response([
                'rent_value' => '0.75', 'unit_value' => '150'
            ]),
        ]);

        $rent = Rent::factory()->create();

        $rent->items()->createMany([
            ['equipment_id' => '3272', 'qty' => 10],
            ['equipment_id' => '3030', 'qty' => 1],
        ]);

        $customer = Customer::factory()->create();

        $response = $this->put(route('rents.update', $rent->id), [
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-20',
            'qty_days' => '10',
            'discount' => '',
            'paid_value' => '',
            'delivery_value' => '',
            'bill' => '',
            'check_info' => '',
            'delivery_address' => '',
            'usage_address' => '',
            'discount_reason' => '',
            'observations' => 'Testing update',
            'transporter' => '',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => 2, 'equipment_id' => '3272'],
            ],
        ], ['accept' => 'application/json']);

        $rent->refresh();
        $response->assertSuccessful();

        $this->assertCount(1, Item::all());
        $this->assertCount(1, $rent->items);
        $this->assertEquals(2, $rent->items[0]->qty);
        $this->assertEquals(0.6, $rent->items[0]->rent_value);
        $this->assertEquals(500, $rent->items[0]->unit_value);

        $this->assertEquals(10, $rent->qty_days);
        $this->assertEquals('bf18eb16-0b38-404a-8916-429638f1d793', $rent->period_id);
        $this->assertEquals($customer->id, $rent->customer_id);
        $this->assertEquals('Testing update', $rent->observations);
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_update_invalid_token(string $token)
    {
        $rent = Rent::factory()->create();
        $customer = Customer::factory()->create();

        $response = $this->withToken($token)->put(route('rents.update', $rent->id), [
            'start_date' => '2020-10-10',
            'end_date' => '2020-10-20',
            'qty_days' => '10',
            'customer_id' => $customer->id,
            'period_id' => 'bf18eb16-0b38-404a-8916-429638f1d793',
            'payment_type_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_method_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'payment_condition_id' => 'b7c09550-2907-459e-9dc5-c2116016bacd',
            'items' => [
                ['qty' => 2, 'equipment_id' => '3272'],
            ],
        ], ['accept' => 'application/json']);

        $response->assertUnauthorized();
    }

    public function test_show_soft_deleted_rent()
    {
        $rent = Rent::factory()->create(['deleted_at' => now()]);

        $response = $this->get(route('rents.show', $rent->id), [
            'accept' => 'application/json',
        ]);

        $response->assertNotFound();
    }

    public function test_show_non_existent_rent()
    {
        $uuid = '62578f05-85f2-442b-8412-df47d188e01b';

        $response = $this->get(route('rents.show', $uuid), [
            'accept' => 'application/json',
        ]);

        $response->assertNotFound();
    }

    public function test_show_rent()
    {
        $rent = Rent::factory()->create();

        $response = $this->get(route('rents.show', $rent->id), [
            'accept' => 'application/json',
        ]);

        $response->assertSuccessful();
        $response->assertJson($rent->refresh()->toArray());
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_show_rent_invalid_token(string $token)
    {
        $rent = Rent::factory()->create();

        $response = $this->withToken($token)->get(route('rents.show', $rent->id), [
            'accept' => 'application/json',
        ]);

        $response->assertUnauthorized();
    }

    public function test_delete_non_existent()
    {
        $uuid = '62578f05-85f2-442b-8412-df47d188e01b';

        $response = $this->delete(route('rents.destroy', $uuid), [], [
            'accept' => 'application/json',
        ]);

        $response->assertNotFound();
    }

    public function test_delete_soft_deleted()
    {
        $rent = Rent::factory()->create(['deleted_at' => now()]);

        $response = $this->delete(route('rents.destroy', $rent->id), [], [
            'accept' => 'application/json',
        ]);

        $response->assertNotFound();
    }

    public function test_delete()
    {
        $rent = Rent::factory()->create();

        $response = $this->delete(route('rents.destroy', $rent->id), [], [
            'accept' => 'application/json',
        ]);

        $response->assertNoContent();
        $this->assertSoftDeleted($rent);
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_delete_invalid_token(string $token)
    {
        $rent = Rent::factory()->create();

        $response = $this->withToken($token)->delete(route('rents.destroy', $rent->id), [], [
            'accept' => 'application/json',
        ]);

        $response->assertUnauthorized();
    }

    public function test_list_ignores_deleted()
    {
        Rent::factory()->count(20)->create();
        Rent::factory()->count(10)->create(['deleted_at' => now()]);

        $response = $this->get(route('rents.index'), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(20, 'items');
    }

    public function test_list_paginates()
    {
        Rent::factory()->count(100)->create();

        $response = $this->get(route('rents.index'), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(50, 'items');
    }

    public function test_list_paginates_per_page()
    {
        Rent::factory()->count(100)->create();

        $response = $this->get(route('rents.index', ['per_page' => 100]), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(100, 'data');
    }

    public function test_list_paginates_per_page_over_limit()
    {
        Rent::factory()->count(100)->create();

        $response = $this->get(route('rents.index', ['per_page' => 150]), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(100, 'items');
    }

    public function test_list_filter_by_customer()
    {
        $customer = Customer::factory()->create();

        Rent::factory()->count(30)->create();
        Rent::factory()->count(20)->create(['customer_id' => $customer->id]);

        $response = $this->get(route('rents.index', ['customer' => $customer->id]), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(20, 'data');
    }

    public function test_list_filter_by_number()
    {
        $rents = Rent::factory()->count(30)->create();

        $response = $this->get(route('rents.index', ['number' => $rents[20]->id]), [
            'accept' => 'application/json'
        ]);

        $response->assertJsonCount(1, 'items');
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_list_invalid_token(string $token)
    {
        Rent::factory()->count(100)->create();

        $response = $this->withToken($token)->get(route('rents.index'), [
            'accept' => 'application/json'
        ]);

        $response->assertUnauthorized();
    }
}
