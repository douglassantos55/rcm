<?php

namespace App\Services;

interface PaymentService extends Service
{
    public function getPaymentType(string $uuid): ?array;

    public function getPaymentMethod(string $uuid): ?array;

    public function getPaymentCondition(string $uuid): ?array;
}
