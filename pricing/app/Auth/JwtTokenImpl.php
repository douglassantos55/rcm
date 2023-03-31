<?php

namespace App\Auth;

use App\Auth\Constraint\Constraint;
use Lcobucci\JWT\UnencryptedToken;

class JwtTokenImpl implements JwtToken
{
    /**
     * @var UnencryptedToken
     */
    private $token;

    public function __construct(UnencryptedToken $token)
    {
        $this->token = $token;
    }

    public function getHeader(): array
    {
        return $this->token->headers()->all();
    }

    public function getPayload(): array
    {
        return $this->token->claims()->all();
    }

    public function getSignature(): string
    {
        return $this->token->signature()->toString();
    }

    public function validate(Constraint ...$constraints): bool
    {
        foreach ($constraints as $constraint) {
            if (!$constraint->validate($this->token)) {
                return false;
            }
        }
        return true;
    }
}
