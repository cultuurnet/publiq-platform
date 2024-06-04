<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\UnblockOpportunity;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;

final class UnblockOpportunityTest extends TestCase
{
    use MockInsightlyClient;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private UnblockOpportunity $unblockOpportunity;

    protected function setUp(): void
    {
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->mockCrmClient();

        $this->unblockOpportunity = new UnblockOpportunity(
            $this->insightlyClient,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_unblocks_an_opportunity(): void
    {
        $integrationId = Uuid::uuid4();

        $insightlyMapping = new InsightlyMapping(
            $integrationId,
            11,
            ResourceType::Opportunity
        );

        $this->insightlyMappingRepository->expects($this->once())
            ->method('getByIdAndType')
            ->with($integrationId, ResourceType::Opportunity)
            ->willReturn($insightlyMapping);

        $this->opportunityResource->expects($this->once())
            ->method('updateState')
            ->with($insightlyMapping->insightlyId, OpportunityState::OPEN);

        $this->unblockOpportunity->handle(new IntegrationUnblocked($integrationId));
    }
}
