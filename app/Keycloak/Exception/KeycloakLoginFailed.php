<?php

declare(strict_types=1);

namespace App\Keycloak\Exception;

use Exception;

final class KeycloakLoginFailed extends Exception
{
    public const STATE_IS_INVALID = 1;
    public const MISSING_CODE = 2;
    public const ISS_MISMATCH = 3;

    private function __construct(
        string $message,
        int $code,
    ) {
        parent::__construct($message, $code);
    }

    public static function stateMismatch(): self
    {
        return new self('State is invalid', self::STATE_IS_INVALID);
    }

    public static function missingCode(): self
    {
        return new self('Missing code in request', self::MISSING_CODE);
    }

    public static function issMismatch(string $url): self
    {
        return new self(sprintf('ISS is not valid, received url: %s', $url), self::ISS_MISMATCH);
    }
}
