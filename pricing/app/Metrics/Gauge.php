<?php

namespace App\Metrics;

interface Gauge
{
    /**
     * Sets the current value
     *
     * @param int|float $value
     * @param array $labels
     *
     * @return void
     */
    public function set($value, array $labels = []);

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
     * @param int|float $amount
     * @param array $labels
     *
     * @return void
     */
    public function incrementBy($amount, array $labels = []);

    /**
     * Decrements value by 1
     *
     * @param array $labels
     *
     * @return void
     */
    public function decrement(array $labels = []);

    /**
     * Decrements value by given amount
     *
     * @param int|float $amount
     * @param array $labels
     *
     * @return void
     */
    public function decrementBy($amount, array $labels = []);
}
