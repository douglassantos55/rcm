<?php

namespace Tests\Unit;

use App\Services\Tracing\ZipkinTracer;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZipkinTest extends TestCase
{
    public function test_returns_callback_return()
    {
        Http::fake(['*' => Http::response()]);
        $renting = new ZipkinTracer('renting', 'http://zipkin:9411/api/v2/spans');

        $result = $renting->trace('test_callback', function () {
            sleep(1);
            return 'foobar';
        });

        $this->assertEquals('foobar', $result);
    }

    public function test_tracing_services()
    {
        $tracer = new ZipkinTracer('renting', 'zipkin/api/v2/spans');

        Http::fake([
            'zipkin/*' => Http::response(),
            'inventory' => function () use ($tracer) {
                $request = app('request');

                // Add context manually because I don't know how to get the
                // request headers to go through perhaps it's something with testing?
                $request->headers->add($tracer->getContext());

                $inventory = new ZipkinTracer('inventory', 'zipkin/api/v2/spans');

                return $inventory->trace('test_inventory', function () {
                    sleep(2);
                    return Http::response('hello from inventory');
                });
            },
        ]);

        $result = $tracer->trace('test_multiple_services', function () use ($tracer) {
            sleep(1);
            return $tracer->trace('test_invoke_inventory', fn () => Http::get('inventory'));
        });

        $this->assertEquals('hello from inventory', $result->body());
    }
}
