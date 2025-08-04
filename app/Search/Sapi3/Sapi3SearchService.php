<?php

declare(strict_types=1);

namespace App\Search\Sapi3;

use App\Domain\UdbUuid;
use App\Search\UiTPAS\UiTPASLabelProvider;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\SearchClientInterface;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;

final readonly class Sapi3SearchService implements SearchService
{
    public function __construct(
        private SearchClientInterface $searchClient,
        private UiTPASLabelProvider $uiTPASLabelProvider,
    ) {
    }

    public function searchUiTPASOrganizer(string $name): PagedCollection
    {
        $searchQuery = new SearchQuery();
        $this->addUiTPASLabels($searchQuery);
        $searchQuery->addParameter(new Name($name));
        $searchQuery->setLimit(5);
        $searchQuery->setEmbed(true);

        return $this->searchClient->searchOrganizers($searchQuery);
    }

    public function findUiTPASOrganizers(UdbUuid ...$ids): PagedCollection
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setEmbed(true);
        if (empty($ids)) {
            return new PagedCollection();
        }

        $ids = array_map(fn (UdbUuid $id) => sprintf('id:"%s"', $id->toString()), $ids);
        $searchQuery->addParameter(new Query(implode(' OR ', $ids)));
        $this->addUiTPASLabels($searchQuery);

        return $this->searchClient->searchOrganizers($searchQuery);
    }


    public function addUiTPASLabels(SearchQuery $searchQuery): void
    {
        $labels = $this->uiTPASLabelProvider->getLabels();
        $searchQuery->addParameter(new Query(implode(' OR ', $labels)));
    }
}
