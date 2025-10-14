<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use Ramsey\Uuid\UuidInterface;

interface UdbOrganizerRepository
{
    public function create(UdbOrganizer $organizer): void;

    public function createInBulk(UdbOrganizers $organizers): void;

    public function updateStatus(UdbOrganizer $organizer, UdbOrganizerStatus $newStatus): void;

    public function delete(UuidInterface $integrationId, UdbUuid $organizerId): void;

    public function getById(UuidInterface $id): UdbOrganizer;

    public function getByOrganizerId(UuidInterface $integrationId, UdbUuid $organizerId): UdbOrganizer;
}
