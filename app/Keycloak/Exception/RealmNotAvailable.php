<?php

declare(strict_types=1);

namespace App\Keycloak\Exception;

use App\Domain\Integrations\Environment;
use Exception;

final class RealmNotAvailable extends Exception
{
    private function __construct(
        string $message
    ) {
        parent::__construct($message);
    }

    public static function realmNotAvailable(Environment $environment): self
    {
        throw new self(sprintf('Could not determine realm with the provided environment %s', $environment->value));
    }
}
