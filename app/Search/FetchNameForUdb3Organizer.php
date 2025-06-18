<?php

declare(strict_types=1);

namespace App\Search;

use CultuurNet\SearchV3\ValueObjects\Organizer;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;

/*
 * If performance starts to be an issue, a cache would be good here.
 * */

final readonly class FetchNameForUdb3Organizer
{
    public function fetchName(PagedCollection $collection): string
    {
        if ($collection->getTotalItems() < 1) {
            return 'Niet teruggevonden in UDB3';
        }

        /** @var Organizer $organizer */
        $organizer = $collection->getMember()?->getItems()[0];

        $langCode = $organizer->getMainLanguage() ?? 'nl';
        return $organizer->getName()?->getValueForLanguage($langCode) ?? '';
    }
}
