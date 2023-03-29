<?php

namespace App\Services;

interface Service
{
    public function has(string $entity, string $identifier): bool;
}
