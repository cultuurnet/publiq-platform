<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\DeleteOrganization;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;

final class DeleteOrganizationTest extends TestCase
{
    use MockInsightlyClient;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private DeleteOrganization $listener;

    protected function setUp(): void
    {
        $this->mockCrmClient(new Pipelines(['opportunities' => ['id' => 3, 'stages' => ['test' => 4]]]));
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->listener = new DeleteOrganization(
            $this->insightlyClient,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function test_it_can_delete_an_organization(): void
    {
        $organizationId = Uuid::uuid4();
        $insightlyId = 1234;

        $insightlyIntegrationMapping = new InsightlyMapping(
            $organizationId,
            $insightlyId,
            ResourceType::Organization,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('getByIdAndType')
            ->with($organizationId, ResourceType::Organization)
            ->willReturn($insightlyIntegrationMapping);

        $this->organizationResource->expects($this->once())
            ->method('delete')
            ->with($insightlyId);

        $this->insightlyMappingRepository->expects(self::once())
            ->method('deleteById')
            ->with($organizationId);

        $event = new OrganizationDeleted($organizationId);
        $this->listener->handle($event);
    }
}
