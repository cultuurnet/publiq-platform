<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;

interface UdbOrganizerRepository
{
    public function create(UdbOrganizer $organizer): void;

    public function createInBulk(UdbOrganizers $organizers): void;

    public function delete(UdbOrganizer $organizer): void;
}
