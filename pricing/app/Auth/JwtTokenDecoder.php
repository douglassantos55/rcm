<?php

namespace App\Auth;

use App\Auth\Constraint\Constraint;
use App\Auth\Exception\InvalidAlgorithmException;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

class JwtTokenDecoder implements JwtDecoder
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Validator
     */
    private $validator;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->parser = new Parser(new JoseEncoder());
    }

    public function decode(string $encoded, string $algo, string $secret, Constraint ...$constraints): ?JwtToken
    {
        try {
            /** @var UnencryptedToken */
            $token = $this->parser->parse($encoded);
            $sign = new SignedWith($this->getAlgorithm($algo), InMemory::plainText($secret));

            if (!$this->validator->validate($token, $sign)) {
                return null;
            }

            $token = new JwtTokenImpl($token);
            if (!$token->validate(...$constraints)) {
                return null;
            }

            return $token;
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $e) {
            logger($e->getMessage(), ['encoded' => $encoded]);
            return null;
        }
    }

    /**
     * Gets the algorithm signer
     *
     * @param string $algo
     *
     * @return Signer
     *
     * @throws InvalidAlgorithmException
     */
    private function getAlgorithm(string $algo): Signer
    {
        return match ($algo) {
            'HS256' => new Sha256(),
            'HS384' => new Sha384(),
            'HS512' => new Sha512(),
            default => throw new InvalidAlgorithmException()
        };
    }
}
