<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationActivatedWithOrganization;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Insightly\SyncIsAllowed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateProjectWithOrganization implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository,
        private readonly OrganizationRepository $organizationRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationActivatedWithOrganization $integrationActivatedWithOrganization): void
    {
        $integrationId = $integrationActivatedWithOrganization->id;

        // Update the state ("open") and stage ("request") of the existing opportunity
        $opportunityMapping = $this->insightlyMappingRepository->getByIdAndType(
            $integrationId,
            ResourceType::Opportunity
        );

        $this->insightlyClient->opportunities()->updateState(
            $opportunityMapping->insightlyId,
            OpportunityState::OPEN
        );
        $this->insightlyClient->opportunities()->updateStage(
            $opportunityMapping->insightlyId,
            OpportunityStage::REQUEST
        );

        // Create a new project with stage ("live") and state ("completed")
        $integration = $this->integrationRepository->getById($integrationId);

        $insightlyProjectId = $this->insightlyClient->projects()->create($integration);
        $this->insightlyMappingRepository->save(
            new InsightlyMapping($integrationId, $insightlyProjectId, ResourceType::Project)
        );

        $this->insightlyClient->projects()->updateState(
            $insightlyProjectId,
            ProjectState::COMPLETED
        );
        $this->insightlyClient->projects()->updateStage(
            $insightlyProjectId,
            ProjectStage::LIVE
        );

        // Link the opportunity to the new project
        $this->insightlyClient->projects()->linkOpportunity(
            $insightlyProjectId,
            $opportunityMapping->insightlyId
        );

        // Link the contacts to the new project
        $linkedContacts = $this->contactRepository->getByIntegrationId($integrationId);
        foreach ($linkedContacts as $linkedContact) {
            if (SyncIsAllowed::forContact($linkedContact)) {
                $insightlyContactMapping = $this->insightlyMappingRepository->getByIdAndType(
                    $linkedContact->id,
                    ResourceType::Contact
                );
                $this->insightlyClient->projects()->linkContact(
                    $insightlyProjectId,
                    $insightlyContactMapping->insightlyId,
                    $linkedContact->type
                );
            }
        }

        // Check if the organization already exists based on the VAT number or invoice email
        //  Yes => use the found organization
        //  No => create a new organization with a contact
        $organization = $this->organizationRepository->getById($integrationId);
        $organizationInsightlyId = $this->findInsightlyOrganization($organization);

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
