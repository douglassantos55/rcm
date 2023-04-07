<?php

namespace App\Services;

use Zipkin\Endpoint;
use Zipkin\Propagation\Map;
use Zipkin\Propagation\SamplingFlags;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Span;
use Zipkin\Tracing;
use Zipkin\TracingBuilder;

class ZipkinTracer implements Tracer
{
    /**
     * @var Tracing
     */
    private $tracing;

    /**
     * @var Span
     */
    private $root;

    public function __construct(string $service, string $zipkinUrl)
    {
        $this->root = null;

        $this->tracing = TracingBuilder::create()
            ->havingLocalEndpoint(Endpoint::create($service))
            ->havingSampler(BinarySampler::createAsAlwaysSample())
            ->havingReporter(new Http(['endpoint_url' => $zipkinUrl . '/api/v2/spans']))
            ->build();
    }

    public function trace(callable $callback): mixed
    {
        $span = $this->getSpan();

        $span->start();
        $result = $callback($this->getContext());
        $span->finish();

        register_shutdown_function(fn () => $this->tracing->getTracer()->flush());
        return $result;
    }

    private function getSpan(): Span
    {
        $tracer = $this->tracing->getTracer();
        $current = $this->root;

        if (is_null($current)) {
            $span = $tracer->nextSpan($this->getRequestContext());
            $span->setKind(\Zipkin\Kind\SERVER);

            $this->root = $span;
        } else {
            $span = $tracer->newChild($current->getContext());
            $span->setKind(\Zipkin\Kind\CLIENT);
        }

        return $span;
    }

    private function getRequestContext(): SamplingFlags
    {
        $headers = request()->headers->all();
        $carrier = array_map(fn ($header) => $header[0], $headers);

        $extractor = $this->tracing->getPropagation()->getExtractor(new Map());
        return $extractor($carrier);
    }

    public function getContext(): array
    {
        $headers = [];

        $injector = $this->tracing->getPropagation()->getInjector(new Map());
        $injector($this->root->getContext(), $headers);

        return $headers;
    }
}
