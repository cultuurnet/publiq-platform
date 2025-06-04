<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Exceptions;

use App\Domain\Integrations\Environment;
use RuntimeException;

final class KeycloakClientNotFound extends RuntimeException
{
    public static function byEnvironment(Environment $environment): self
    {
        return new self(
            sprintf('Keycloak client not found for env %s', $environment->value)
        );
    }
}
