<?php

namespace App\Metrics;

interface Histogram
{
    /**
     * Adds an observation value to the histogram
     *
     * @param int|float $value
     * @param array $labels
     *
     * @return void
     */
    public function observe($value, array $labels = []);
}
