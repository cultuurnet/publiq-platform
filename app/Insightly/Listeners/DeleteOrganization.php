<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Insightly\InsightlyClient;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class DeleteOrganization implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(OrganizationDeleted $organizationDeleted): void
    {
        $insightlyMapping = $this->insightlyMappingRepository->getByIdAndType(
            $organizationDeleted->id,
            ResourceType::Organization
        );

        $this->insightlyClient->organizations()->delete($insightlyMapping->insightlyId);

        $this->insightlyMappingRepository->deleteById($organizationDeleted->id);

        $this->logger->info(
            'Organization deleted',
            [
                'domain' => 'insightly',
                'organization_id' => $organizationDeleted->id->toString(),
            ]
        );
    }

    public function failed(OrganizationDeleted $organizationDeleted, \Throwable $exception): void
    {
        $this->logger->error(
            'Failed to delete organization',
            [
                'domain' => 'insightly',
                'organization_id' => $organizationDeleted->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
