<?php

namespace App\Metrics\Prometheus;

use App\Metrics\Histogram as MetricsHistogram;
use Prometheus\Histogram as PrometheusHistogram;

class Histogram implements MetricsHistogram
{
    /**
     * @var PrometheusHistogram
     */
    private $histogram;

    public function __construct(PrometheusHistogram $histogram)
    {
        $this->histogram = $histogram;
    }

    public function observe($value, array $labels = [])
    {
        $this->histogram->observe($value, $labels);
    }
}
