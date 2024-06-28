<?php

declare(strict_types=1);

namespace App\Search\Sapi3;

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
        $searchQuery->setLimit(5);
        $searchQuery->setEmbed(true);

        return $this->searchClient->searchOrganizers($searchQuery);
    }
}
