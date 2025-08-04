<?php

declare(strict_types=1);

namespace App\Search\Sapi3;

use App\Domain\UdbUuid;
use App\Json;
use App\UiTPAS\UiTPASConfig;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\SearchClientInterface;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use Illuminate\Contracts\Cache\Repository as Cache;

final readonly class Sapi3SearchService implements SearchService
{
    public function __construct(
        private SearchClientInterface $searchClient,
        private Cache $cache
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
        $searchQuery->addParameter(new Query(implode(' OR ', $this->getUiTPasLabels())));
    }

    private function getUiTPasLabels(): array
    {
        return $this->cache->remember('uitpas_labels', now()->addHours(24), function () {
            $uri = config(UiTPASConfig::UDB_IO_BASE_URI->value) . 'uitpas/labels';
            $content = Json::decodeAssociatively((string)file_get_contents($uri));

            return array_map(
                static fn (string $value) => 'labels:' . $value,
                $content
            );
        });
    }
}
