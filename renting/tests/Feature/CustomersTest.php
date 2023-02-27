<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomersTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_validation()
    {
        $response = $this->post(route('customers.store'), [
            'name' => '',
            'cpf_cnpj' => '220.000.000-00',
            'state' => 'BRL',
            'email' => 'Somethingotherthananemail',
            'postcode' => '32505_2222',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'name' => 'The name field is required.',
            'email' => 'The email field must be a valid email address.',
            'cpf_cnpj' => 'The cpf cnpj is invalid.',
            'state' => 'The state field must be 2 characters.',
            'postcode' => 'The postcode field format is invalid.',
        ]);
    }

    public function test_duplicated_email()
    {
        Customer::factory()->create(['email' => 'john@email.com']);

        $response = $this->post(route('customers.store'), [
            'name' => 'John Doe',
            'email' => 'john@email.com',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'email' => 'The email has already been taken.',
        ]);
    }

    public function test_create_duplicated_cpf()
    {
        Customer::factory()->create(['cpf_cnpj' => '297.164.260-70']);

        $response = $this->post(route('customers.store'), [
            'name' => 'John Doe',
            'email' => 'john@email.com',
            'cpf_cnpj' => '297.164.260-70',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'cpf_cnpj' => 'The cpf cnpj has already been taken.',
        ]);
    }

    public function test_create_duplicated_cnpj()
    {
        Customer::factory()->create(['cpf_cnpj' => '20643221000195']);

        $response = $this->post(route('customers.store'), [
            'name' => 'John Doe',
            'email' => 'john@email.com',
            'cpf_cnpj' => '20643221000195',
        ], ['accept' => 'application/json']);

        $response->assertJsonValidationErrors([
            'cpf_cnpj' => 'The cpf cnpj has already been taken.',
        ]);
    }

    public function test_create()
    {
        $response = $this->post(route('customers.store'), [
            'name' => 'Joaquim',
        ], ['accept' => 'application/json']);

        $response->assertCreated();
        $this->assertModelExists(Customer::where('name', 'Joaquim')->first());
    }
}
