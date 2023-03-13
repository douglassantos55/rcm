<?php

namespace Tests\Unit;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_appends_equipment()
    {
        Http::fake(['*' => Http::response(['id' => 'aoeu'])]);

        $item = Item::factory()->create();

        $this->assertEquals(['id' => 'aoeu'], $item->equipment);
    }

    public function test_appends_equipment_not_found()
    {
        Http::fake(['*' => Http::response(null, 404)]);

        $item = Item::factory()->create();

        $this->assertEquals(null, $item->equipment);
    }

    public function test_appends_equipment_server_error()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $item = Item::factory()->create();

        $this->assertEquals(null, $item->equipment);
    }
}
