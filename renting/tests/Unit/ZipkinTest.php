<?php

namespace Tests\Unit;

use App\Services\ZipkinTracer;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZipkinTest extends TestCase
{
    public function test_returns_callback_return()
    {
        Http::fake(['*' => Http::response()]);
        $renting = new ZipkinTracer('renting', 'http://zipkin:9411/api/v2/spans');

        $result = $renting->trace(function () {
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

                return $inventory->trace(function () {
                    sleep(2);
                    return Http::response('hello from inventory');
                });
            },
        ]);

        $result = $tracer->trace(function () use ($tracer) {
            sleep(1);
            return $tracer->trace(fn () => Http::get('inventory'));
        });

        $this->assertEquals('hello from inventory', $result->body());
    }
}
