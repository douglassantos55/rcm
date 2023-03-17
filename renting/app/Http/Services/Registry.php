<?php

namespace App\Http\Services;

interface Registry
{
    /**
     * Returns the service's address
     *
     * @param string $service The name of the service to look for
     *
     * @return string The address of the service
     */
    public function get(string $service): string;
}
