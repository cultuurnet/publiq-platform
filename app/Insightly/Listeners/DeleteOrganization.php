<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Organizations\Events\OrganizationDeleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class DeleteOrganization implements ShouldQueue
{
    use Queueable;

    public function handle(OrganizationDeleted $organizationDeleted): void
    {
        $insightlyMapping = $this->insightlyMappingRepository->getById($organizationDeleted->id);

        $this->insightlyClient->organizations()->delete($insightlyMapping->insightlyId);

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
