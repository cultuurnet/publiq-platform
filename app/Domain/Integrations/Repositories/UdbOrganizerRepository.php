<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Integrations\UdbOrganizerStatus;
use Ramsey\Uuid\UuidInterface;

interface UdbOrganizerRepository
{
    public function create(UdbOrganizer $organizer): void;

    public function createInBulk(UdbOrganizers $organizers): void;

    public function updateStatus(string $organizerId, UdbOrganizerStatus $newStatus): void;

    public function delete(UdbOrganizer $organizer): void;

    public function getById(UuidInterface $id): UdbOrganizer;
}
