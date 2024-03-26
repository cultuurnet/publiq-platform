<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Insightly\InsightlyClient;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Throwable;

final class DeleteProject implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationDeleted $integrationDeleted): void
    {
        try {
            $integrationId = $integrationDeleted->id;

            $insightlyMapping = $this->insightlyMappingRepository->getByIdAndType(
                $integrationId,
                ResourceType::Project
            );

            $this->insightlyClient->projects()->updateState(
                $insightlyMapping->insightlyId,
                ProjectState::ABANDONED
            );

            $this->insightlyClient->projects()->updateStage(
                $insightlyMapping->insightlyId,
                ProjectStage::ENDED
            );

            $this->logger->info(
                'Project deleted',
                [
                    'domain' => 'insightly',
                    'integration_id' => $integrationId->toString(),
                ]
            );
        } catch (ModelNotFoundException) {
        }
    }

    public function failed(
        IntegrationDeleted $integrationDeleted,
        Throwable $exception
    ): void {
        $this->logger->error(
            'Failed to delete project',
            [
                'domain' => 'insightly',
                'contact_id' => $integrationDeleted->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
