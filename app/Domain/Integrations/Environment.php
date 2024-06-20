<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum Environment: string
{
    case Acceptance = 'acc';
    case Testing = 'test';
    case Production = 'prod';

    public static function fromName(string $input): ?self
    {
        // Input is based on the APP_ENV env variable, which does not match 100%
        if ($input === 'local') {
            return self::Acceptance;
        }

        $input = strtolower($input);

        foreach (self::cases() as $case) {
            if (strtolower($case->name) === $input) {
                return $case;
            }
        }

        return null;
    }
}
