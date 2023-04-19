<?php

namespace Tests\Unit;

use App\Models\Rent;
use App\Services\Registry\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
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
        });
    }

    public function test_items()
    {
        $rent = Rent::factory()->hasItems(5)->create();

        Http::fake([
            '*' => function (Request $request) use ($rent) {
                if (false === strpos($request->url(), '?')) {
                    return Http::response([
                        'id' => substr($request->url(), strrpos($request->url(), '/') + 1),
                        'description' => 'andaime',
                        'rent_value' => 0.55,
                        'unit_value' => 550,
                    ]);
                }

                return Http::response(array_map(function ($item) {
                    return [
                        'id' => $item->equipment_id,
                        'description' => 'item ' . $item->id,
                        'rent_value' => 0.55,
                        'unit_value' => 550,
                    ];
                }, $rent->items->all()));
            }
        ]);


        $this->get(route('rents.show', $rent->id));

        Http::assertSentCount(1);
    }
}
