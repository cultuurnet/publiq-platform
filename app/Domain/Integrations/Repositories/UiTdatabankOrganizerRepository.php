<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\UdbOrganizer;

interface UiTdatabankOrganizerRepository
{
    public function create(UdbOrganizer ...$organizer): void;

    public function delete(UdbOrganizer $organizer): void;
}
