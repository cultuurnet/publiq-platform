<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\UiTdatabankOrganizer;

interface OrganizerRepository
{
    public function create(UiTdatabankOrganizer ...$organizer): void;

    public function delete(UiTdatabankOrganizer $organizer): void;
}
