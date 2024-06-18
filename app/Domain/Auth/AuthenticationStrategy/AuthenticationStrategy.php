<?php

declare(strict_types=1);

namespace App\Domain\Auth\AuthenticationStrategy;

use Illuminate\Http\Request;

interface AuthenticationStrategy
{
    public function getLoginUrl(array $loginParams): string;

    public function exchange(Request $request): bool;

    public function getUser(): ?array;

    public function getIdToken(): string;
}
