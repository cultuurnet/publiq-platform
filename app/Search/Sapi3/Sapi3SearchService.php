<?php

declare(strict_types=1);

namespace App\Search\Sapi3;

use CultuurNet\SearchV3\Parameter\Id;
use CultuurNet\SearchV3\Parameter\Label;
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
        $searchQuery->addParameter(new Label('UiTPAS'));
        $searchQuery->addParameter(new Name($name));
        $searchQuery->setEmbed(true);

        return $this->searchClient->searchOrganizers($searchQuery);
    }

    public function findUiTPASOrganizers(string ...$ids): PagedCollection
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setEmbed(true);
        foreach ($ids as $id) {
            $searchQuery->addParameter(new Id($id));
        }

        return $this->searchClient->searchOrganizers($searchQuery);
    }
}
