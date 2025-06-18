<?php

declare(strict_types=1);

namespace App\Search\Sapi3;

use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\SearchClientInterface;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;

final readonly class Sapi3SearchService implements SearchService
{
    public function __construct(private SearchClientInterface $searchClient)
    {
    }

    public function searchUiTPASOrganizer(string $name): PagedCollection
    {
        $searchQuery = new SearchQuery();
        $searchQuery->addParameter(new Query('labels:UiTPAS*'));
        $searchQuery->addParameter(new Name($name));
        $searchQuery->setLimit(5);
        $searchQuery->setEmbed(true);

        return $this->searchClient->searchOrganizers($searchQuery);
    }

    public function findUiTPASOrganizers(string ...$ids): PagedCollection
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setEmbed(true);
        if (empty($ids)) {
            return new PagedCollection();
        }

        //@todo I wonder if there is a bug here, because the function has a constraint on labels:uitpas, but this one does not?
        $ids = array_map(fn (string $id) => sprintf('id:"%s"', $id), $ids);
        $searchQuery->addParameter(new Query(implode(' OR ', $ids)));

        return $this->searchClient->searchOrganizers($searchQuery);
    }
}
