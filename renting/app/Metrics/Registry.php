<?php

namespace App\Metrics;

interface Registry
{
    /**
     * Creates or returns an existing counter by name
     *
     * @param string $name
     * @param string $namespace
     * @param array $labels
     *
     * @return Counter
     */
    public function getOrCreateCounter(string $name, string $namespace = '', array $labels = []): Counter;

    /**
     * Creates or returns an existing gauge by name
     *
     * @param string $name
     * @param string $namespace
     * @param array $labels
     *
     * @return Gauge
     */
    public function getOrCreateGauge(string $name, string $namespace = '', array $labels = []): Gauge;

    /**
     * Creates or returns an existing histogram by name
     *
     * @param string $name
     * @param string $namespace
     * @param array $labels
     * @param array $buckets
     *
     * @return Histogram
     */
    public function getOrCreateHistogram(string $name, string $namespace = '', array $labels = [], array $buckets = []): Histogram;
}
