<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppliersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_create_unauthorized(string $token)
    {
        $response = $this->withToken($token)->post(route('suppliers.store'), [
            'social_name' => 'Joaquim',
            'cnpj' => '20643221000195',
            'email' => 'myemail@gmail.com',
            'website' => 'https://google.com',
            'state' => 'NY',
        ], ['accept' => 'application/json']);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_update_unauthorized(string $token)
    {
        $supplier = Supplier::factory()->create([
            'social_name' => 'Test',
            'cnpj' => '20.643.221/0001-95'
        ]);

        $response = $this->withToken($token)->put(route('suppliers.update', $supplier->id), [
            'social_name' => 'Joaquim',
            'cnpj' => '20643221000195',
            'email' => 'myemail@gmail.com',
            'website' => 'https://google.com',
            'state' => 'NY',
        ], ['accept' => 'application/json']);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_delete_unauthorized(string $token)
    {
        $supplier = Supplier::factory()->create([
            'social_name' => 'Test',
            'cnpj' => '20.643.221/0001-95'
        ]);

        $route = route('suppliers.destroy', $supplier->id);

        $response = $this->withToken($token)->delete($route, [], [
            'accept' => 'application/json'
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_get_unauthorized(string $token)
    {
        $supplier = Supplier::factory()->create([
            'social_name' => 'Test',
            'cnpj' => '20.643.221/0001-95'
        ]);

        $route = route('suppliers.show', $supplier->id);

        $response = $this->withToken($token)->get($route, [
            'accept' => 'application/json'
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidTokensProvider
     */
    public function test_list_unauthorized(string $token)
    {
        $response = $this->withToken($token)->get(route('suppliers.index'), [
            'accept' => 'application/json'
        ]);

        $response->assertUnauthorized();
    }

    public function test_validation(): void
    {
        $response = $this->post(route('suppliers.store'), [
            'social_name' => '',
            'cnpj' => '000000000000',
            'email' => 'myemailatgmail.com',
            'website' => 'google.com',
            'state' => 'BRL',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'social_name' => 'The social name field is required.',
            'cnpj' => 'The cnpj is invalid.',
            'email' => 'The email field must be a valid email address.',
            'website' => 'The website field must be a valid URL.',
            'state' => 'The state field must be 2 characters.',
        ]);
    }

    public function test_create(): void
    {
        $response = $this->post(route('suppliers.store'), [
            'social_name' => 'Joaquim',
            'cnpj' => '20643221000195',
            'email' => 'myemail@gmail.com',
            'website' => 'https://google.com',
            'state' => 'NY',
            'observations' => 'Testing creation',
        ], ['accept' => 'application/json']);

        $response->assertStatus(201);
        $supplier = Supplier::where('social_name', 'Joaquim')->first();

        $this->assertModelExists($supplier);
        $this->assertEquals('Testing creation', $supplier->observations);
    }

    public function test_create_unique_cnpj()
    {
        Supplier::factory()->create([
            'social_name' => 'Test',
            'cnpj' => '20.643.221/0001-95'
        ]);

        $response = $this->post(route('suppliers.store'), [
            'social_name' => 'Abracadabra',
            'cnpj' => '20.643.221/0001-95',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'cnpj' => 'The cnpj has already been taken.',
        ]);
    }

    public function test_update(): void
    {
        $supplier = Supplier::factory()->create([
            'social_name' => 'Test',
            'cnpj' => '20.643.221/0001-95',
            'observations' => 'Factoried',
        ]);

        $response = $this->put(route('suppliers.update', $supplier->id), [
            'social_name' => 'Updated',
            'cnpj' => '29.039.173/0001-03',
            'observations' => 'Testing update',
        ], ['accept' => 'application/json']);

        $response->assertOk();
        $supplier->refresh();

        $this->assertEquals('Updated', $supplier->social_name);
        $this->assertEquals('Testing update', $supplier->observations);
    }

    public function test_update_unique_cnpj(): void
    {
        $suppliers = Supplier::factory()->createMany([
            [
                'social_name' => 'Test',
                'cnpj' => '20.643.221/0001-95'
            ],
            [
                'social_name' => 'Testing',
                'cnpj' => '29.039.173/0001-03'
            ],
        ]);

        $route = route('suppliers.update', $suppliers[0]->id);

        $response = $this->put($route, [
            'cnpj' => '29.039.173/0001-03',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'cnpj' => 'The cnpj has already been taken.',
        ]);
    }

    public function test_delete()
    {
        $supplier = Supplier::factory()->create([
            'social_name' => 'Test',
            'cnpj' => '20.643.221/0001-95'
        ]);

        $route = route('suppliers.destroy', $supplier->id);

        $response = $this->delete($route, [], [
            'accept' => 'application/json',
        ]);

        $response->assertStatus(204);
        $this->assertSoftDeleted($supplier);
    }

    public function test_list()
    {
        Supplier::factory()->count(300)->create();

        $response = $this->get(route('suppliers.index'), [
            'accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJsonCount(50, 'items');
        $this->assertEquals(300, $response['total']);
    }

    public function test_filter_by_social_name()
    {
        Supplier::factory()->count(100)->create();
        Supplier::factory()->create(['social_name' => 'John Doe']);
        Supplier::factory()->create(['social_name' => 'Jane Doe']);
        Supplier::factory()->create(['social_name' => 'James Doe']);

        $response = $this->get(route('suppliers.index', ['social_name' => 'doe']), [
            'accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJsonCount(3, 'items');
        $this->assertEquals(3, $response['total']);
    }

    public function test_filter_by_cnpj()
    {
        Supplier::factory()->count(100)->create();
        Supplier::factory()->create(['cnpj' => '94.462.105/0001-06']);
        Supplier::factory()->create(['cnpj' => '84.549.644/0001-23']);
        Supplier::factory()->create(['cnpj' => '12.961.974/0001-10']);

        $response = $this->get(route('suppliers.index', ['cnpj' => '10']), [
            'accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJsonCount(2, 'items');
        $this->assertEquals(2, $response['total']);
    }

    public function test_filter_by_email()
    {
        Supplier::factory()->count(100)->create();
        Supplier::factory()->create(['email' => 'johndoe@email.com']);
        Supplier::factory()->create(['email' => 'janedoe@gmail.com']);
        Supplier::factory()->create(['email' => 'jamesdoe@hotmail.com']);

        $response = $this->get(route('suppliers.index', ['email' => 'ja']), [
            'accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJsonCount(2, 'items');
        $this->assertEquals(2, $response['total']);
    }

    public function test_filter_by_legal_name_ignored()
    {
        Supplier::factory()->count(100)->create();
        Supplier::factory()->create(['legal_name' => 'John Doe Inc']);
        Supplier::factory()->create(['legal_name' => 'Jane Doe SA']);
        Supplier::factory()->create(['legal_name' => 'James Doe Ltda']);

        $response = $this->get(route('suppliers.index', ['legal_name' => 'Doe']), [
            'accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJsonCount(50, 'items');
        $this->assertEquals(103, $response['total']);
    }
}
