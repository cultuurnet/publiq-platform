<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class CreateOrganization implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient            $insightlyClient,
        private readonly OrganizationRepository     $organizationRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface            $logger
    ) {
    }

    public function handle(OrganizationCreated $organizationCreated): void
    {
        $organization = $this->organizationRepository->getById($organizationCreated->id);

        $organizationInsightlyId = $this->insightlyClient->organizations()->create($organization);
        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $organizationCreated->id,
            $organizationInsightlyId,
            ResourceType::Organization
        ));

        $this->logger->info(
            'Organization created',
            [
                'domain' => 'insightly',
                'organization_id' => $organizationCreated->id->toString(),
            ]
        );
    }

    public function failed(OrganizationCreated $organizationCreated, \Throwable $exception): void
    {
        $this->logger->error(
            'Failed to create organization',
            [
                'domain' => 'insightly',
                'organization_id' => $organizationCreated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
