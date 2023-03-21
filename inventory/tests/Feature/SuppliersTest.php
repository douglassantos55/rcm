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
        $response = $this->withToken($this->validToken)->post(route('suppliers.store'), [
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
        $response = $this->withToken($this->validToken)->post(route('suppliers.store'), [
            'social_name' => 'Joaquim',
            'cnpj' => '20643221000195',
            'email' => 'myemail@gmail.com',
            'website' => 'https://google.com',
            'state' => 'NY',
        ], ['accept' => 'application/json']);

        $response->assertStatus(201);

        $supplier = Supplier::where('social_name', 'Joaquim')->first();
        $this->assertModelExists($supplier);
    }

    public function test_create_unique_cnpj()
    {
        Supplier::factory()->create([
            'social_name' => 'Test',
            'cnpj' => '20.643.221/0001-95'
        ]);

        $response = $this->withToken($this->validToken)->post(route('suppliers.store'), [
            'social_name' => 'Abracadabra',
            'cnpj' => '20.643.221/0001-95',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'cnpj' => 'The cnpj has already been taken.',
        ]);
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

        $response = $this->withToken($this->validToken)->put($route, [
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

        $response = $this->withToken($this->validToken)->delete($route, [], [
            'accept' => 'application/json',
        ]);

        $response->assertStatus(204);
        $this->assertSoftDeleted($supplier);
    }
}
