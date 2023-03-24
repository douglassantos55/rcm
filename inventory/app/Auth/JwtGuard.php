<?php

namespace App\Auth;

use App\Auth\Constraint\IntendedForConstraint;
use App\Auth\Constraint\IssuedByConstraint;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class JwtGuard implements Guard
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var JwtDecoder
     */
    private $decoder;

    /**
     * @var Authenticatable|null
     */
    private $user;

    public function __construct(Request $request, JwtTokenDecoder $decoder)
    {
        $this->user = null;
        $this->decoder = $decoder;
        $this->request = $request;
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function guest()
    {
        return !$this->check();
    }

    public function user()
    {
        $header = $this->request->header('Authorization');

        if (is_null($header)) {
            return null;
        }

        $tokenStr = str_ireplace('Bearer ', '', $header);

        $constraints = [
            new IssuedByConstraint(config('auth.jwt.issuer')),
            new IntendedForConstraint(config('auth.jwt.audience')),
        ];

        $token = $this->decoder->decode($tokenStr, 'HS256', config('auth.jwt.secret'), ...$constraints);

        if (is_null($token)) {
            return null;
        }

        $payload = collect($token->getPayload());

        $this->user = new User([
            'id' => $payload->get('sub'),
            'name' => $payload->get('name'),
            'email' => $payload->get('email'),
        ]);

        return $this->user;
    }

    public function id()
    {
        if (!is_null($this->user)) {
            return $this->user->getAuthIdentifier();
        }

        return null;
    }

    public function hasUser()
    {
        return !is_null($this->user);
    }

    // Does nothing since it's not intended to authenticate here
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
    }

    // Does nothing since it's not intended to authenticate here
    public function validate(array $credentials = [])
    {
        return false;
    }
}
