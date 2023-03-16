<?php

namespace App\Http\Services;

interface Registry
{
    /**
     * Registers a service
     *
     * @param string $name
     */
    public function register(string $name): void;

    /**
     * Returns the service's address
     *
     * @param string $service The name of the service to look for
     *
     * @return string The address of the service
     */
    public function getAddress(string $service): string;
}
