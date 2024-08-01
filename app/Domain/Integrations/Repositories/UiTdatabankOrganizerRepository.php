<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\UiTdatabankOrganizer;

interface UiTdatabankOrganizerRepository
{
    public function create(UiTdatabankOrganizer ...$organizer): void;

    public function delete(UiTdatabankOrganizer $organizer): void;
}
