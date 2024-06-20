<?php

declare(strict_types=1);

namespace App\Keycloak;

use DateInterval;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory as InMemoryKey;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

/* Class is copied from UDB3 code base: src/Http/Authentication/JsonWebToken.php*/
final class JsonWebToken
{
    private UnencryptedToken $token;

    /** @throws InvalidTokenStructure */
    public function __construct(string $jwt)
    {
        if(empty($jwt)) {
            throw new InvalidTokenStructure('JWT is missing');
        }

        $token = (new Parser(new JoseEncoder()))->parse($jwt);
        // Need this assert to make PHPstan happy
        assert($token instanceof UnencryptedToken, 'Token should be an instance of UnencryptedToken');
        $this->token = $token;
    }

    public function validate(string $publicKey, ?string $keyPassphrase = null): bool
    {
        if(empty($publicKey)) {
            throw new InvalidTokenStructure('Public key is missing');
        }

        $signer = new Sha256();
        $key = InMemoryKey::plainText($publicKey, (string)$keyPassphrase);

        return (new Validator())->validate(
            $this->token,
            new LooseValidAt(
                new SystemClock(
                    new DateTimeZone('Europe/Brussels')
                ),
                new DateInterval('PT30S')
            ),
            new SignedWith($signer, $key)
        );
    }

    public function getToken(): UnencryptedToken
    {
        return $this->token;
    }
}
