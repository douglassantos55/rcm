<?php

namespace App\Auth;

use App\Auth\Constraint\Constraint;

interface JwtDecoder
{
    /**
     * Decodes and validates a JWT token
     *
     * @param string $encoded The encoded token
     * @param string $algo The algorithm used to encode token
     * @param string $secret The secret used to encode token
     * @param Constraint $constraints The constraints to validate
     *
     * @return JwtToken|null
     */
    public function decode(string $encoded, string $algo, string $secret, Constraint ...$constraints): ?JwtToken;
}
