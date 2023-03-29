<?php

namespace App\Services;

interface InventoryService extends Service
{
    public function getEquipment(string $uuid): ?array;
}
