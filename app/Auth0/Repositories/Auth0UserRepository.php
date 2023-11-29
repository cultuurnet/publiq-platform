<?php

declare(strict_types=1);

namespace App\Auth0\Repositories;

interface Auth0UserRepository
{
    public function findUserIdByEmail(string $email): ?string;
}
