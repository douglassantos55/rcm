<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppliersTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->post(route('suppliers.store'), [
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

        $response = $this->put(route('suppliers.update', $suppliers[0]->id), [
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

        $response = $this->delete(route('suppliers.destroy', $supplier->id));

        $response->assertStatus(204);
        $this->assertSoftDeleted($supplier);
    }
}
