<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\UnblockProject;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;

final class UnblockProjectTest extends TestCase
{
    use MockInsightlyClient;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private IntegrationRepository&MockObject $integrationRepository;

    private UnblockProject $unblockProject;

    protected function setUp(): void
    {
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->mockCrmClient();

        $this->unblockProject = new UnblockProject(
            $this->insightlyClient,
            $this->insightlyMappingRepository,
            $this->integrationRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_unblocks_a_project(): void
    {
        $integrationId = Uuid::uuid4();

        $insightlyMapping = new InsightlyMapping(
            $integrationId,
            11,
            ResourceType::Project
        );

        $this->insightlyMappingRepository->expects($this->once())
            ->method('getByIdAndType')
            ->with($integrationId, ResourceType::Project)
            ->willReturn($insightlyMapping);

        $this->insightlyClient->expects($this->once())
            ->method('projects')
            ->willReturn($this->projectResource);

        $this->projectResource->expects($this->once())
            ->method('updateState')
            ->with($insightlyMapping->insightlyId, ProjectState::COMPLETED);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn(
                new Integration(
                    $integrationId,
                    IntegrationType::EntryApi,
                    'foo',
                    'bar',
                    Uuid::uuid4(),
                    IntegrationStatus::Active,
                    IntegrationPartnerStatus::THIRD_PARTY
                )
            );


        $this->unblockProject->handle(new IntegrationUnblocked($integrationId));
    }
}
