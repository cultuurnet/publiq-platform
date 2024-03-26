<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Repositories;

use App\Domain\Integrations\KeyVisibility;

interface ContactKeyVisibilityRepository
{
    public function forEmail(string $email): KeyVisibility;
}
