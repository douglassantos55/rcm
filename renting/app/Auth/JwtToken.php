<?php

namespace App\Auth;

interface JwtToken
{
    /**
     * Returns token header
     *
     * @return array
     */
    public function getHeader(): array;

    /**
     * Returns token payload
     *
     * @return array
     */
    public function getPayload(): array;

    /**
     * Returns token signature
     *
     * @return string
     */
    public function getSignature(): string;
}
