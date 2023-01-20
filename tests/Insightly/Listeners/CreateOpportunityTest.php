<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateOpportunity;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;
use Tests\UuidFactoryTest;

final class CreateOpportunityTest extends TestCase
{
    use MockInsightlyClient;

    private IntegrationRepository&MockObject $integrationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private CreateOpportunity $listener;

    private UuidInterface $insightlyMappingOpportunityId;

    protected function setUp(): void
    {
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->mockCrmClient();

        $this->insightlyMappingOpportunityId = Uuid::fromString('f717ea13-a86a-4df0-b70c-496278c92732');

        $this->listener = new CreateOpportunity(
            $this->insightlyClient,
            $this->integrationRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
            new UuidFactoryTest($this->insightlyMappingOpportunityId),
        );
    }

    public function test_it_creates_an_opportunity(): void
    {
        // Given
        $integrationId = Uuid::uuid4();
        $insightlyId = 42;

        $integration = $this->givenThereIsAnIntegrationWithId($integrationId);

        // Then it creates the opportunity at Insightly
        $this->opportunityResource->expects($this->once())
            ->method('create')
            ->with($integration)
            ->willReturn($insightlyId);

        // Then it stores the insightlyId mapping
        $insightlyIntegrationMapping = new InsightlyMapping(
            $this->insightlyMappingOpportunityId,
            $integrationId,
            $insightlyId,
            ResourceType::Opportunity,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($insightlyIntegrationMapping);

        // When
        $event = new IntegrationCreated($integrationId);
        $this->listener->handle($event);
    }

    private function givenThereIsAnIntegrationWithId(UuidInterface $integrationId): Integration
    {
        $integration = new Integration(
            $integrationId,
            IntegrationType::EntryApi,
            'my little integration',
            'a little integration',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            [],
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        return $integration;
    }
}
