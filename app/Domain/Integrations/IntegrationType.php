<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum IntegrationType: string
{
    case EntryApi = 'entry-api';
    case SearchApi = 'search-api';
    case Widgets = 'widgets';
    case UiTPAS = 'uitpas';
}
