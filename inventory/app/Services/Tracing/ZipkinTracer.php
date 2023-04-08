<?php

namespace App\Services\Tracing;

use Closure;
use Symfony\Component\HttpFoundation\Response;
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

    public function trace(string $name, Closure $callback): mixed
    {
        $span = $this->getSpan();

        $span->start();
        $span->setName($name);

        $response = $callback($this->getContext());

        if ($response instanceof Response) {
            $span->tag(\Zipkin\Tags\HTTP_STATUS_CODE, (string) $response->getStatusCode());

            if ($response->isServerError() && $response->exception) {
                $span->setError(new \Exception($response->exception->getMessage()));
            }
        }

        $span->finish();

        register_shutdown_function(fn () => $this->tracing->getTracer()->flush());

        return $response;
    }

    private function getSpan(): Span
    {
        $tracer = $this->tracing->getTracer();
        $current = $this->root;

        if (is_null($current)) {
            $span = $tracer->nextSpan($this->getRequestContext());
            $span->setKind(\Zipkin\Kind\SERVER);
            $span->annotate(\Zipkin\Annotations\WIRE_RECV);

            $this->root = $span;
        } else {
            $span = $tracer->newChild($current->getContext());
            $span->setKind(\Zipkin\Kind\CLIENT);
            $span->annotate(\Zipkin\Annotations\WIRE_SEND);
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
