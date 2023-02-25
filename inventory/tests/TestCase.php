<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** @var string */
    protected $validToken;

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

}
