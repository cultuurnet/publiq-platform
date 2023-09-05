<?php

declare(strict_types=1);

namespace App\UiTiDv1;

enum UiTiDv1ConsumerStatus: string
{
    case Active = 'ACTIVE';
    case Blocked = 'BLOCKED';
    case Unknown = 'UNKNOWN';

    public static function fromValue(string $name): self
    {
        foreach (self::cases() as $status) {
            if ($name === $status->value) {
                return $status;
            }
        }

        return self::Unknown;
    }
}
