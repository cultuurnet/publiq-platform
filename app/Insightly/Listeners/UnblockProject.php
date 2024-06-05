<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Insightly\InsightlyClient;
use App\Insightly\IntegrationStatusConverter;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Throwable;

final class UnblockProject implements ShouldQueue
{
    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationUnblocked $integrationUnblocked): void
    {
        try {
            $integrationId = $integrationUnblocked->id;

            $insightlyMapping = $this->insightlyMappingRepository->getByIdAndType(
                $integrationId,
                ResourceType::Project
            );

            $this->insightlyClient->projects()->updateState(
                $insightlyMapping->insightlyId,
                IntegrationStatusConverter::getProjectState($integrationUnblocked->status) ?? ProjectState::COMPLETED,
            );

            $this->logger->info(
                'Project unblocked',
                [
                    'domain' => 'insightly',
                    'integration_id' => $integrationId->toString(),
                ]
            );
        } catch (ModelNotFoundException) {
        }
    }

    public function failed(
        IntegrationUnblocked $integrationUnblocked,
        Throwable $exception
    ): void {
        $this->logger->error(
            'Failed to unblock project',
            [
                'domain' => 'insightly',
                'contact_id' => $integrationUnblocked->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
