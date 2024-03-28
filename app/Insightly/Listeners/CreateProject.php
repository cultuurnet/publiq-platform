<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\UuidInterface;

// @deprecated
final class CreateProject
{
    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly CouponRepository $couponRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
    ) {
    }

    public function forIntegration(UuidInterface $integrationId, bool $withCoupon): int
    {
        // Update the state and stage of the existing opportunity
        $opportunityMapping = $this->insightlyMappingRepository->getByIdAndType(
            $integrationId,
            ResourceType::Opportunity
        );

        $this->insightlyClient->opportunities()->updateState(
            $opportunityMapping->insightlyId,
            $withCoupon ? OpportunityState::WON : OpportunityState::OPEN
        );
        $this->insightlyClient->opportunities()->updateStage(
            $opportunityMapping->insightlyId,
            $withCoupon ? OpportunityStage::CLOSED : OpportunityStage::REQUEST
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

        try {
            $coupon = $this->couponRepository->getByIntegrationId($integration->id);
        } catch (ModelNotFoundException) {
            $coupon = null;
        }

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
                );
            }
        }

        return $insightlyProjectId;
    }
}
