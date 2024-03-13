<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationActivatedWithOrganization;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateProjectWithOrganization implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CreateProject $createProject,
        private readonly InsightlyClient $insightlyClient,
        private readonly OrganizationRepository $organizationRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationActivatedWithOrganization $integrationActivatedWithOrganization): void
    {
        $integrationId = $integrationActivatedWithOrganization->id;

        $insightlyProjectId = $this->createProject->forIntegration($integrationId, false);

        $organization = $this->organizationRepository->getByIntegrationId($integrationId);
        try {
            $organizationMapping = $this->insightlyMappingRepository->getByIdAndType(
                $organization->id,
                ResourceType::Organization
            );

            $organizationInsightlyId = $organizationMapping->insightlyId;
        } catch (ModelNotFoundException) {
            $organizationInsightlyId = $this->findInsightlyOrganization($organization);
        }

        if ($organizationInsightlyId === null) {
            $organizationInsightlyId = $this->insightlyClient->organizations()->create($organization);

            $organizationMapping = new InsightlyMapping(
                $organization->id,
                $organizationInsightlyId,
                ResourceType::Organization
            );
            $this->insightlyMappingRepository->save($organizationMapping);
        }

        // Link the organization
        $this->insightlyClient->projects()->linkOrganization(
            $insightlyProjectId,
            $organizationInsightlyId
        );

        $this->logger->info(
            'Project created for integration activated for with',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationActivatedWithOrganization->id->toString(),
            ]
        );
    }

    public function failed(
        IntegrationActivatedWithOrganization $integrationActivatedWithOrganization,
        Throwable $exception
    ): void {
        $this->logger->error(
            'Failed to activate integration with organization',
            [
                'domain' => 'insightly',
                'contact_id' => $integrationActivatedWithOrganization->id->toString(),
                'exception' => $exception,
            ]
        );
    }

    private function findInsightlyOrganization(Organization $organization): ?int
    {
        $organizationInsightlyId = null;

        if ($organization->vat !== null) {
            $organizationInsightlyId = $this->insightlyClient->organizations()->findIdByVat(
                $organization->vat
            );
        }

        if ($organizationInsightlyId === null) {
            $organizationInsightlyId = $this->insightlyClient->organizations()->findIdByEmail(
                $organization->invoiceEmail
            );
        }

        return $organizationInsightlyId;
    }
}
