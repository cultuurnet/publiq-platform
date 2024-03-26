<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\DeleteOpportunity;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;
use Tests\TestCase;

final class DeleteOpportunityTest extends TestCase
{
    use MockInsightlyClient;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private LoggerInterface&MockObject $logger;

    private DeleteOpportunity $deleteOpportunity;

    protected function setUp(): void
    {
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->mockCrmClient();

        $this->deleteOpportunity = new DeleteOpportunity(
            $this->insightlyClient,
            $this->insightlyMappingRepository,
            $this->logger
        );
    }

    public function test_it_deletes_an_opportunity(): void
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
            ->with($insightlyMapping->insightlyId, OpportunityState::ABANDONED);

        $this->opportunityResource->expects($this->once())
            ->method('updateStage')
            ->with($insightlyMapping->insightlyId, OpportunityStage::CLOSED);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                'Opportunity deleted',
                [
                    'domain' => 'insightly',
                    'integration_id' => $integrationId->toString(),
                ]
            );

        $this->deleteOpportunity->handle(new IntegrationDeleted($integrationId));
    }
}
