<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Exceptions;

use App\Domain\UdbUuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

final class UdbOrganizerAlreadyExists extends RuntimeException
{
    public static function onIntegration(UuidInterface $integrationId, UdbUuid $organizerId): self
    {
        return new self(
            sprintf('Organizer %s is already added to integration %s', $organizerId, $integrationId->toString())
        );
    }
}
