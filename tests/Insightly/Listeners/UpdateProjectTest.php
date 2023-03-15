<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\UpdateProject;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;

final class UpdateProjectTest extends TestCase
{
    use MockInsightlyClient;

    private IntegrationRepository&MockObject $integrationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private UpdateProject $updateProject;

    protected function setUp(): void
    {
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->mockCrmClient();

        $this->updateProject = new UpdateProject(
            $this->insightlyClient,
            $this->integrationRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_updates_a_project(): void
    {
        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            []
        );

        $insightlyMapping = new InsightlyMapping(
            $integration->id,
            11,
            ResourceType::Project
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integration->id)
            ->willReturn($integration);

        $this->insightlyMappingRepository->expects($this->once())
            ->method('getByIdAndType')
            ->with($integration->id, ResourceType::Project)
            ->willReturn($insightlyMapping);

        $this->projectResource->expects($this->once())
            ->method('update')
            ->with($insightlyMapping->insightlyId, $integration);

        $this->updateProject->handle(new IntegrationUpdated($integration->id));
    }
}
