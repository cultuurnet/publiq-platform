<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final class ActivateProject implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly ContactRepository $contactRepository,
        private readonly OrganizationRepository $organizationRepository,
        private readonly CouponRepository $couponRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationActivated|IntegrationActivationRequested $integrationActivated): void
    {
        $integrationId = $integrationActivated->id;

        try {
            $coupon = $this->couponRepository->getByIntegrationId($integrationId);
        } catch (ModelNotFoundException) {
            $coupon = null;
        }

        $insightlyProjectId = $this->createProject($integrationId, $coupon);

        $this->linkOrganization($integrationId, $insightlyProjectId);

        if ($coupon !== null) {
            $this->linkCoupon($integrationId, $insightlyProjectId, $coupon);
        }

        $this->logger->info(
            'Project activated for integration',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationId->toString(),
                'insightly_project_id' => $insightlyProjectId,
            ]
        );
    }

    private function createProject(UuidInterface $integrationId, ?Coupon $coupon): int
    {
        // Update the state and stage of the existing opportunity
        $opportunityMapping = $this->insightlyMappingRepository->getByIdAndType(
            $integrationId,
            ResourceType::Opportunity
        );

        $this->insightlyClient->opportunities()->updateState(
            $opportunityMapping->insightlyId,
            OpportunityState::WON
        );
        $this->insightlyClient->opportunities()->updateStage(
            $opportunityMapping->insightlyId,
            OpportunityStage::CLOSED
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

        $this->insightlyClient->projects()->updateSubscription(
            $insightlyProjectId,
            $this->subscriptionRepository->getById($integration->subscriptionId),
            $coupon
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

        return $insightlyProjectId;
    }

    private function linkOrganization(UuidInterface $integrationId, int $insightlyProjectId): void
    {
        try {
            $organization = $this->organizationRepository->getByIntegrationId($integrationId);
        } catch (ModelNotFoundException) {
            $this->logger->info(
                'Organization not found for activated project',
                [
                    'domain' => 'insightly',
                    'integration_id' => $integrationId->toString(),
                ]
            );
            return;
        }

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

        $this->insightlyClient->projects()->linkOrganization(
            $insightlyProjectId,
            $organizationInsightlyId
        );

        $this->logger->info(
            'Organization linked to activated project',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationId->toString(),
                'insightly_project_id' => $insightlyProjectId,
                'organization_id' => $organization->id->toString(),
                'insightly_organization_id' => $organizationInsightlyId,
            ]
        );
    }

    private function linkCoupon(UuidInterface $integrationId, int $insightlyProjectId, Coupon $coupon): void
    {
        $this->insightlyClient->projects()->updateWithCoupon(
            $insightlyProjectId,
            $coupon->code
        );

        $this->logger->info(
            'Coupon linked to activated project',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationId->toString(),
                'insightly_project_id' => $insightlyProjectId,
                'coupon_code' => $coupon->code,
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

        if ($organizationInsightlyId === null && $organization->invoiceEmail !== null) {
            $organizationInsightlyId = $this->insightlyClient->organizations()->findIdByEmail(
                $organization->invoiceEmail
            );
        }

        return $organizationInsightlyId;
    }

    public function failed(
        IntegrationActivated|IntegrationActivationRequested $integrationActivated,
        Throwable $exception
    ): void {
        $this->logger->error(
            'Failed project activation',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationActivated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
