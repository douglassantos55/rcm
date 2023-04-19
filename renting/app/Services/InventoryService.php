<?php

namespace App\Services;

interface InventoryService extends Service
{
    /**
     * Fetch equipment from inventory service
     *
     * @param array|string $uuid The UUID of the equipment or an array of UUIDs
     *
     * @return array|null
     */
    public function getEquipment(mixed $uuid): ?array;
}
