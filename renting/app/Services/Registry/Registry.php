<?php

namespace App\Services\Registry;

interface Registry
{
    /**
     * Returns the service's address
     *
     * @param string $service The name of the service to look for
     *
     * @return array The available instances for the service
     */
    public function get(string $service): array;
}
