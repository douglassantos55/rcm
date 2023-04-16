<?php

namespace App\Services\Balancer;

interface Balancer
{
/**
 * Selects an instance to use
 *
 * @param array<int, string> $instances
 *
 * @return string The instance address
 */
    public function get(array $instances): string;
}
