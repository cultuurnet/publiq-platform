<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Search\Sapi3\SearchService;
use CultuurNet\SearchV3\ValueObjects\Organizer;

/*
 * If performance starts to be an issue, a cache would be good here.
 * */

final readonly class FetchNameForUdb3Organizer
{
    public function __construct(private SearchService $searchService)
    {
    }

    public function fetchName(string $organizerId): string
    {
        $result = $this->searchService->findUiTPASOrganizers($organizerId);

        if ($result->getTotalItems() < 1) {
            return 'Niet teruggevonden in UDB3';
        }

        /** @var Organizer $organizer */
        $organizer = $result->getMember()?->getItems()[0];

        $langCode = $organizer->getMainLanguage() ?? 'nl';
        return $organizer->getName()?->getValueForLanguage($langCode) ?? '';
    }
}
