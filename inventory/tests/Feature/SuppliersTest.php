<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppliersTest extends TestCase
{
    use RefreshDatabase;

    /** @var string */
    private $validToken;

    public function setUp(): void
    {
        parent::setUp();
        $this->validToken = 'eyJhbGciOiJIUzI1NiIsInR5cGUiOiJKV1QifQ.eyJpc3MiOiJhdXRoX3NlcnZpY2UiLCJhdWQiOiJyZWNvbmNpcCIsInN1YiI6ImJhMDY4MjI4LTY5ZmQtNDJmNi1hMjQyLTBkZmE0YTI2OWM1ZCIsIm5hbWUiOiJKb2huIERvZSIsImVtYWlsIjoiam9obmRvZUBlbWFpbC5jb20ifQ.PvauHoiC6PrQ0piBghkOSzUm-fkbxClJUNzGujPYi1M';
    }


    public function invalidTokensProvider()
    {
        return [
            // invalid issuer
            ['eyJhbGciOiJIUzI1NiIsInR5cGUiOiJKV1QifQ.eyJpc3MiOiJhdXRoIiwiYXVkIjoicmVjb25jaXAiLCJzdWIiOiJiYTA2ODIyOC02OWZkLTQyZjYtYTI0Mi0wZGZhNGEyNjljNWQiLCJuYW1lIjoiSm9obiBEb2UiLCJlbWFpbCI6ImpvaG5kb2VAZW1haWwuY29tIn0.ypB4oZE_b45GQiWl6Oefv8rQDWHG2gN1AjTK6wX4suE'],
            // invalid audience
            ['eyJhbGciOiJIUzI1NiIsInR5cGUiOiJKV1QifQ.eyJpc3MiOiJhdXRoX3NlcnZpY2UiLCJhdWQiOiJhbnlvbmUiLCJzdWIiOiJiYTA2ODIyOC02OWZkLTQyZjYtYTI0Mi0wZGZhNGEyNjljNWQiLCJuYW1lIjoiSm9obiBEb2UiLCJlbWFpbCI6ImpvaG5kb2VAZW1haWwuY29tIn0.WPGJtYANIu3Wx1hcs1lyrwxYvGdc2IFY2R2QUbUmh_k'],
            // invalid algorithm
            ['eyJhbGciOiJIUzUxMiIsInR5cGUiOiJKV1QifQ.eyJpc3MiOiJhdXRoX3NlcnZpY2UiLCJhdWQiOiJyZWNvbmNpcCIsInN1YiI6ImJhMDY4MjI4LTY5ZmQtNDJmNi1hMjQyLTBkZmE0YTI2OWM1ZCIsIm5hbWUiOiJKb2huIERvZSIsImVtYWlsIjoiam9obmRvZUBlbWFpbC5jb20ifQ.JoUPlJBXV4xMgjYcNIUa9-q-UXy8ycxS09Q8VReIetOoYLOQ6AwW_yOL9xG6xASr4YSSc-z2xZVgy1ZmFZGwSw'],
            // invalid issuer and audience
            ['eyJhbGciOiJIUzI1NiIsInR5cGUiOiJKV1QifQ.eyJpc3MiOiJhdXRoIiwiYXVkIjoiYW55b25lIiwic3ViIjoiYmEwNjgyMjgtNjlmZC00MmY2LWEyNDItMGRmYTRhMjY5YzVkIiwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJqb2huZG9lQGVtYWlsLmNvbSJ9.hTJ5U0jPj0U-JqL9SqTJYt892F8LhE1c8M6o_8lUvOs'],
            // invalid issuer, audience and algorithm
            ['eyJhbGciOiJIUzM4NCIsInR5cGUiOiJKV1QifQ.eyJpc3MiOiJhdXRoIiwiYXVkIjoiYW55b25lIiwic3ViIjoiYmEwNjgyMjgtNjlmZC00MmY2LWEyNDItMGRmYTRhMjY5YzVkIiwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJqb2huZG9lQGVtYWlsLmNvbSJ9.BmN4wnyLCNAf-ANtObySHvgeqcUmMQNHxAhF-XCLfotQOH_F1SiZgb6HVBy2n-Ei'],
        ];
    }

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
