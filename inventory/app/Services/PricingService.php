<?php

namespace App\Services;

use Illuminate\Http\Response;

interface PricingService
{
    public function getRentingValues(string $equipment): array;

    public function createRentingValues(array $values): Response;

    public function updateRentingValues(array $values): Response;
}
