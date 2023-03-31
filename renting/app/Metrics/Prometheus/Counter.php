<?php

namespace App\Metrics\Prometheus;

use App\Metrics\Counter as MetricsCounter;
use Prometheus\Counter as PrometheusCounter;

class Counter implements MetricsCounter
{
    /**
     * @var PrometheusCounter
     */
    private $counter;

    public function __construct(PrometheusCounter $counter)
    {
        $this->counter = $counter;
    }

    public function increment(array $labels = [])
    {
        $this->counter->inc($labels);
    }

    public function incrementBy(int $amount, array $labels = [])
    {
        $this->counter->incBy($amount, $labels);
    }
}
