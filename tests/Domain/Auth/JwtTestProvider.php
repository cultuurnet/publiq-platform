<?php

declare(strict_types=1);

namespace Tests\Domain\Auth;

final class JwtTestProvider
{
    private string $jwt;

    public function __construct()
    {
        $this->jwt = (string)file_get_contents(__DIR__ . '/example.jwt');
    }

    public function getJwt(): string
    {
        return $this->jwt;
    }
}
