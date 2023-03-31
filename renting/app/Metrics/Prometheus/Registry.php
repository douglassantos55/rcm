<?php

namespace App\Metrics\Prometheus;

use App\Metrics\Registry as MetricsRegistry;
use App\Metrics\Counter;
use App\Metrics\Gauge;
use App\Metrics\Histogram;
use App\Metrics\Prometheus\Counter as PrometheusCounter;
use App\Metrics\Prometheus\Gauge as PrometheusGauge;
use App\Metrics\Prometheus\Histogram as PrometheusHistogram;
use Prometheus\CollectorRegistry;

class Registry implements MetricsRegistry
{
    /**
     * @var CollectorRegistry
     */
    private $registry;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getOrCreateGauge(string $name, string $namespace = '', array $labels = []): Gauge
    {
        $gauge = $this->registry->getOrRegisterGauge($namespace, $name, '', $labels);
        return new PrometheusGauge($gauge);
    }

    public function getOrCreateCounter(string $name, string $namespace = '', array $labels = []): Counter
    {
        $counter = $this->registry->getOrRegisterCounter($namespace, $name, '', $labels);
        return new PrometheusCounter($counter);
    }

    public function getOrCreateHistogram(string $name, string $namespace = '', array $labels = [], array $buckets = []): Histogram
    {
        $histogram = $this->registry->getOrRegisterHistogram($namespace, $name, '', $labels, $buckets);
        return new PrometheusHistogram($histogram);
    }
}
