<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\Repositories\InsightlyMappingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class UpdateOrganization implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly OrganizationRepository $organizationRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(OrganizationUpdated $organizationUpdated): void
    {
        $organization = $this->organizationRepository->getById($organizationUpdated->id);

        $insightlyMapping = $this->insightlyMappingRepository->getById($organization->id);

        $this->insightlyClient->organizations()->update($organization, $insightlyMapping->insightlyId);

        $this->logger->info(
            'Organization updated',
            [
                'domain' => 'insightly',
                'contact_id' => $organizationUpdated->id->toString(),
            ]
        );
    }

    public function failed(OrganizationUpdated $organizationCreated, \Throwable $exception): void
    {
        $this->logger->error(
            'Failed to update organization',
            [
                'domain' => 'insightly',
                'contact_id' => $organizationCreated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
