<?php

namespace App\Http\Services;

interface Service
{
    public function has(string $entity, string $identifier): bool;
}
