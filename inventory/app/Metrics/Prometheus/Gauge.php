<?php

namespace App\Metrics\Prometheus;

use App\Metrics\Gauge as MetricsGauge;
use Prometheus\Gauge as PrometheusGauge;

class Gauge implements MetricsGauge
{
    /**
     * @var PrometheusGauge
     */
    private $gauge;

    public function __construct(PrometheusGauge $gauge)
    {
        $this->gauge = $gauge;
    }

    public function set($value, array $labels = [])
    {
        $this->gauge->set($value, $labels);
    }

    public function increment(array $labels = [])
    {
        $this->gauge->inc($labels);
    }

    public function incrementBy($amount, array $labels = [])
    {
        $this->gauge->incBy($amount, $labels);
    }

    public function decrement(array $labels = [])
    {
        $this->gauge->dec($labels);
    }

    public function decrementBy($amount, array $labels = [])
    {
        $this->gauge->decBy($amount, $labels);
    }
}
