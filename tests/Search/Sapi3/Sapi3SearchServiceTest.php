<?php

declare(strict_types=1);

namespace Tests\Search\Sapi3;

use App\Domain\UdbUuid;
use App\Search\Sapi3\Sapi3SearchService;
use App\Search\UiTPAS\UiTPASLabelProvider;
use CultuurNet\SearchV3\SearchClientInterface;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class Sapi3SearchServiceTest extends TestCase
{
    public function test_search_uiTPAS_organizer_adds_name_and_labels(): void
    {
        $labelProvider = $this->createMock(UiTPASLabelProvider::class);
        $labelProvider->expects($this->once())
            ->method('getLabels')
            ->willReturn(['labels:uitpas-gent', 'labels:uitpas-antwerpen']);
        $searchClient = $this->createMock(SearchClientInterface::class);
        $searchClient->expects($this->once())
            ->method('searchOrganizers')
            ->with($this->callback(function (SearchQuery $result) {
                $params = $result->toArray();

                return $params['name'] === 'test organizer' &&
                    str_contains($params['q'], 'labels:uitpas-gent') &&
                    str_contains($params['q'], 'labels:uitpas-antwerpen') &&
                    $params['limit'] === 5 &&
                    $params['embed'] === true;
            }))
            ->willReturn(new PagedCollection());

        (new Sapi3SearchService($searchClient, $labelProvider))
            ->searchUiTPASOrganizer('test organizer');
    }

    public function test_find_uiTPAS_organizers_adds_ids_and_labels(): void
    {
        $labelProvider = $this->createMock(UiTPASLabelProvider::class);
        $labelProvider->expects($this->once())
            ->method('getLabels')
            ->willReturn(['labels:uitpas-antwerpen']);

        $uuid1 = new UdbUuid(Uuid::uuid4()->toString());
        $uuid2 = new UdbUuid(Uuid::uuid4()->toString());

        $searchClient = $this->createMock(SearchClientInterface::class);
        $searchClient->expects($this->once())
            ->method('searchOrganizers')
            ->with($this->callback(function (SearchQuery $result) use ($uuid1, $uuid2) {
                $params = $result->toArray();

                return str_contains($params['q'], 'id:"' . $uuid1->toString() . '"') &&
                    str_contains($params['q'], 'id:"' . $uuid2->toString() . '"') &&
                    str_contains($params['q'], 'labels:uitpas-antwerpen') &&
                    $params['embed'] === true;
            }))
            ->willReturn(new PagedCollection());

        (new Sapi3SearchService($searchClient, $labelProvider))
            ->findUiTPASOrganizers($uuid1, $uuid2);
    }
}
