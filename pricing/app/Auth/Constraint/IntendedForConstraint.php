<?php

namespace App\Auth\Constraint;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\ConstraintViolation;

class IntendedForConstraint implements Constraint
{
    /**
     * @var PermittedFor
     */
    private $constraint;

    public function __construct(string $audience)
    {
        $this->constraint = new PermittedFor($audience);
    }

    public function validate($token): bool
    {
        try {
            if (!$token instanceof Token) {
                return false;
            }
            $this->constraint->assert($token);
            return true;
        } catch (ConstraintViolation) {
            return false;
        }
    }
}
