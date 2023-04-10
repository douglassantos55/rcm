<?php


namespace Tests\Unit;

use App\Services\Registry\HttpConsulRegistry;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpConsulRegistryTest extends TestCase
{

    public function test_get_unknown_service()
    {
        Http::fake(['consul/*' => Http::response(null, 404)]);
        $registry = new HttpConsulRegistry('consul');

        $this->assertThrows(fn () => $registry->get('inventory'));
    }

    public function test_get_server_error()
    {
        Http::fake(['consul/*' => Http::response(null, 500)]);
        $registry = new HttpConsulRegistry('consul');

        $this->assertThrows(fn () => $registry->get('inventory'));
    }

    public function test_get_critical_status()
    {
        Http::fake([
            'consul/*' => Http::response([
                ['AggregatedStatus' => 'critial'],
            ]),
        ]);

        $registry = new HttpConsulRegistry('consul');
        $this->assertThrows(fn () => $registry->get('inventory'));
    }

    public function test_get_warning_status()
    {
        Http::fake([
            'consul/*' => Http::response([
                ['AggregatedStatus' => 'warning'],
                ['AggregatedStatus' => 'critical'],
                ['AggregatedStatus' => 'critical'],
            ]),
        ]);

        $registry = new HttpConsulRegistry('consul');
        $this->assertThrows(fn () => $registry->get('inventory'));
    }

    public function test_get_passing_status()
    {
        Http::fake([
            'consul/*' => Http::response([
                ['AggregatedStatus' => 'warning'],
                ['AggregatedStatus' => 'critical'],
                [
                    'AggregatedStatus' => 'passing',
                    'Service' => ['Address' => '127.0.0.1:8000'],
                ],
            ]),
        ]);

        $registry = new HttpConsulRegistry('consul');
        $this->assertEquals('127.0.0.1:8000', $registry->get('inventory'));
    }
}
