<?php

declare(strict_types=1);

namespace Tests\Search\Sapi3;

use App\Domain\UdbUuid;
use App\Search\Sapi3\Name;
use App\Search\Sapi3\Sapi3SearchService;
use App\Search\UiTPAS\UiTPASLabelProvider;
use CultuurNet\SearchV3\Parameter\Query;
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
            ->willReturn(['uitpas gent', 'uitpas antwerpen']);
        $searchClient = $this->createMock(SearchClientInterface::class);
        $searchClient->expects($this->once())
            ->method('searchOrganizers')
            ->with($this->callback(function (SearchQuery $result) {
                $q = new SearchQuery(true);
                $q->addParameter(new Query('labels:"uitpas gent" OR labels:"uitpas antwerpen"'));
                $q->setLimit(5);
                $q->addParameter(new Name('test organizer'));

                $this->assertSame($q->toArray(), $result->toArray());
                return true;
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
            ->willReturn(['uitpas gent', 'uitpas antwerpen']);

        $uuid1 = new UdbUuid(Uuid::uuid4()->toString());
        $uuid2 = new UdbUuid(Uuid::uuid4()->toString());

        $searchClient = $this->createMock(SearchClientInterface::class);
        $searchClient->expects($this->once())
            ->method('searchOrganizers')
            ->with($this->callback(function (SearchQuery $result) use ($uuid1, $uuid2) {
                $q = new SearchQuery(true);
                $q->addParameter(new Query(sprintf('id:"%s" OR id:"%s"', $uuid1->toString(), $uuid2->toString())));
                $q->addParameter(new Query('labels:"uitpas gent" OR labels:"uitpas antwerpen"'));

                $this->assertSame($result->toArray(), $q->toArray());
                return true;
            }))
            ->willReturn(new PagedCollection());

        (new Sapi3SearchService($searchClient, $labelProvider))
            ->findUiTPASOrganizers($uuid1, $uuid2);
    }
}
