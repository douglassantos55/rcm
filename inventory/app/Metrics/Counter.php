<?php

namespace App\Metrics;

interface Counter
{
    /**
     * Increments value by 1
     *
     * @param array $labels
     *
     * @return void
     */
    public function increment(array $labels = []);

    /**
     * Increments value by given amount
     *
     * @param int $amount
     * @param array $labels
     *
     * @return void
     */
    public function incrementBy(int $amount, array $labels = []);
}
