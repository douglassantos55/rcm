<?php

namespace App\Auth;

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
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Lcobucci\JWT\Validator as JWTValidator;

class JwtTokenDecoder implements JwtDecoder
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var JWTValidator
     */
    private $validator;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->parser = new Parser(new JoseEncoder());
    }

    public function decode(string $encoded, string $algo, string $secret): ?JwtToken
    {
        try {
            /** @var UnencryptedToken */
            $token = $this->parser->parse($encoded);

            $issuer = new IssuedBy('auth_service');
            $audience = new PermittedFor('reconcip');
            $sign = new SignedWith($this->getAlgorithm($algo), InMemory::plainText($secret));

            if (!$this->validator->validate($token, $issuer, $audience, $sign)) {
                return null;
            }

            return new JwtTokenImpl($token);
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $e) {
            logger($e->getMessage(), ['encoded' => $encoded]);
            return null;
        }
    }

    private function getAlgorithm(string $algorithm): Signer
    {
        switch ($algorithm) {
            case 'HS256':
                return new Sha256();
            case 'HS384':
                return new Sha384();
            case 'HS512':
                return new Sha512();
            default:
                throw new InvalidAlgorithmException();
        }
    }
}
