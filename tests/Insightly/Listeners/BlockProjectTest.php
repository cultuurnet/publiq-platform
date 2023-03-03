<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\BlockProject;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;

final class BlockProjectTest extends TestCase
{
    use MockInsightlyClient;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private BlockProject $blockProject;

    protected function setUp(): void
    {
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->mockCrmClient();

        $this->blockProject = new BlockProject(
            $this->insightlyClient,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_blocks_a_project(): void
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
            ->with($insightlyMapping->insightlyId, ProjectState::CANCELLED);

        $this->blockProject->handle(new IntegrationBlocked($integrationId));
    }
}
