<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum IntegrationType: string
{
    case EntryApi = 'entry-api';
    case SearchApi = 'search-api';
    case Widgets = 'widgets';
    case UiTPAS = 'uitpas';

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        throw new \InvalidArgumentException('Invalid integration type: ' . $name);
    }
}
