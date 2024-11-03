<?php

declare(strict_types=1);

namespace App\Domain\Auth\Repositories;

interface UserRepository
{
    public function findUserIdByEmail(string $email): ?string;
}
