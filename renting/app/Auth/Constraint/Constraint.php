<?php

namespace App\Auth\Constraint;

interface Constraint
{
    /**
     * Validates constraint for given token
     *
     * @param mixed $token
     *
     * @return bool
     */
    public function validate($token): bool;
}
