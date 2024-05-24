<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum Environment: string
{
    case Acceptance = 'acc';
    case Testing = 'test';
    case Production = 'prod';

    public static function fromString(string $string): self
    {
        return match ($string) {
            'acceptance' => self::Acceptance,
            'testing' => self::Testing,
            'production' => self::Production,
            default => throw new \InvalidArgumentException(sprintf('Invalid environment: %s', $string)),
        };
    }
}
