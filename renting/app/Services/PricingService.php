<?php

namespace App\Services;

interface PricingService extends Service
{
    public function getPeriod(string $uuid): ?array;

    public function getRentingValues(string $equipmentUuid): ?array;
}
