<?php

declare(strict_types=1);

namespace Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\DeleteProject;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;
use Tests\TestCase;

final class DeleteProjectTest extends TestCase
{
    use MockInsightlyClient;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private LoggerInterface&MockObject $logger;

    private DeleteProject $deleteProject;

    protected function setUp(): void
    {
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->mockCrmClient();

        $this->deleteProject = new DeleteProject(
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
            ResourceType::Project
        );

        $this->insightlyMappingRepository->expects($this->once())
            ->method('getByIdAndType')
            ->with($integrationId, ResourceType::Project)
            ->willReturn($insightlyMapping);

        $this->projectResource->expects($this->once())
            ->method('updateState')
            ->with($insightlyMapping->insightlyId, ProjectState::ABANDONED);

        $this->projectResource->expects($this->once())
            ->method('updateStage')
            ->with($insightlyMapping->insightlyId, ProjectStage::ENDED);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                'Project deleted',
                [
                    'domain' => 'insightly',
                    'integration_id' => $integrationId->toString(),
                ]
            );

        $this->deleteProject->handle(new IntegrationDeleted($integrationId));
    }
}
