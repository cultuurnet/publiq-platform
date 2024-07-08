<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Organizer;

interface OrganizerRepository
{
    public function create(Organizer ...$organizer): void;

    public function delete(Organizer $organizer): void;
}
