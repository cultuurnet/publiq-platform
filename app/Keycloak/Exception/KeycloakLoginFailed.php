<?php

declare(strict_types=1);

namespace App\Keycloak\Exception;

use Exception;

final class KeycloakLoginFailed extends Exception
{
    private function __construct(
        string $message
    ) {
        parent::__construct($message);
    }

    public static function stateMismatch(): self
    {
        return new self('State is invalid');
    }

    public static function missingCode(): self
    {
        return new self('Missing code in request');
    }

    public static function issMismatch(string $url): self
    {
        return new self(sprintf('ISS is not valid, received url: %s', $url));
    }
}
