<?php

namespace App\Services;

interface InventoryService extends Service
{
    /**
     * Fetch equipment from inventory service. Implementations should cache
     * the results for a brief period of time in order to avoid a bunch of
     * requests going to the service at once.
     *
     * @param array|string $uuid The UUID of the equipment or an array of UUIDs
     *
     * @return array|null
     */
    public function getEquipment(mixed $uuid): ?array;
}
