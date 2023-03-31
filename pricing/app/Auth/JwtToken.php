<?php

namespace App\Auth;

use App\Auth\Constraint\Constraint;

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

    /**
     * Validates token for given constraints
     *
     * @param Constraint $constraints
     *
     * @return bool
     */
    public function validate(Constraint ...$constraints): bool;
}

