<?php

namespace App\Auth\Constraint;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\ConstraintViolation;

class IssuedByConstraint implements Constraint
{
    /**
     * @var IssuedBy
     */
    private $constraint;

    public function __construct(string $issuer)
    {
        $this->constraint = new IssuedBy($issuer);
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
