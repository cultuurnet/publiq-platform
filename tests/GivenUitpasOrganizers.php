<?php

declare(strict_types=1);

namespace Tests;

use CultuurNet\SearchV3\ValueObjects\Collection;
use CultuurNet\SearchV3\ValueObjects\Organizer as SapiOrganizer;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;

trait GivenUitpasOrganizers
{
    private function givenUitpasOrganizers(string $organizerId, string $name, int $itemCount): PagedCollection
    {
        $org = new SapiOrganizer();
        $org->setId($organizerId);
        $org->setName(new TranslatedString(['nl' => $name]));

        $collection = new Collection();
        $collection->setItems([$org]);

        $pagedCollection = new PagedCollection();
        $pagedCollection->setMember($collection);
        $pagedCollection->setTotalItems($itemCount);

        return $pagedCollection;
    }
}
