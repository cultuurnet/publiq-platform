<?php

declare(strict_types=1);

namespace App\Search\Sapi3;

use App\Domain\Udb3Uuid;
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

    public function findUiTPASOrganizers(Udb3Uuid ...$ids): PagedCollection
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setEmbed(true);
        if (empty($ids)) {
            return new PagedCollection();
        }

        $ids = array_map(fn (Udb3Uuid $id) => sprintf('id:"%s"', $id->toString()), $ids);
        $searchQuery->addParameter(new Query(implode(' OR ', $ids)));

        return $this->searchClient->searchOrganizers($searchQuery);
    }
}
