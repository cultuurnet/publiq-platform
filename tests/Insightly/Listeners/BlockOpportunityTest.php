<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\BlockOpportunity;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;

final class BlockOpportunityTest extends TestCase
{
    use MockInsightlyClient;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private BlockOpportunity $blockOpportunity;

    protected function setUp(): void
    {
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->mockCrmClient();

        $this->blockOpportunity = new BlockOpportunity(
            $this->insightlyClient,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_blocks_an_opportunity(): void
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
            ->with($insightlyMapping->insightlyId, OpportunityState::SUSPENDED);

        $this->blockOpportunity->handle(new IntegrationBlocked($integrationId));
    }
}
