<?php

namespace App\Auth;

interface JwtDecoder
{
    /**
     * Decodes a JWT token
     *
     * @param string $encoded The encoded token
     * @param string algo The algorithm used to sign the token
     * @param string $secret The secret used to sign the token
     *
     * @return JwtToken|null
     */
    public function decode(string $encoded, string $algo, string $secret): ?JwtToken;
}
