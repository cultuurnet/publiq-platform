<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait MockRepositories
{
    private ContactRepository&MockObject $contactRepository;

    private CouponRepository&MockObject $couponRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private IntegrationRepository&MockObject $integrationRepository;

    private function mockRepositories(): void
    {
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
    }

    private function givenThereAreContacts(UuidInterface $integrationId): Collection
    {
        $contacts = [
            new Contact(
                $this->contributorContactId,
                $integrationId,
                'jan@mail.com',
                ContactType::Contributor,
                'Jan',
                'Desmet'
            ),
            new Contact(
                $this->technicalContactId,
                $integrationId,
                'an@mail.com',
                ContactType::Technical,
                'An',
                'Deraaf'
            ),
            new Contact(
                $this->functionalContactId,
                $integrationId,
                'piet@mail.com',
                ContactType::Functional,
                'Piet',
                'Dedonder'
            ),
        ];

        $contactCollection = new Collection($contacts);

        $this->contactRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($contactCollection);

        return $contactCollection;
    }

    private function givenThereIsACoupon(UuidInterface $integrationId): Coupon
    {
        $coupon = new Coupon(
            Uuid::uuid4(),
            true,
            $integrationId,
            $this->couponCode,
        );

        $this->couponRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($coupon);

        return $coupon;
    }

    private function givenThereAreInsightlyMappings(
        int $insightlyOpportunityId,
        int $insightlyTechnicalId,
        int $insightlyFunctionalId
    ): void {
        $insightlyIntegrationMapping = new InsightlyMapping(
            $this->integrationId,
            $insightlyOpportunityId,
            ResourceType::Opportunity,
        );

        $insightlyTechnicalContactMapping = new InsightlyMapping(
            $this->technicalContactId,
            $insightlyTechnicalId,
            ResourceType::Contact,
        );

        $insightlyFunctionalContactMapping = new InsightlyMapping(
            $this->functionalContactId,
            $insightlyFunctionalId,
            ResourceType::Contact,
        );

        $this->insightlyMappingRepository->expects($this->exactly(3))
            ->method('getByIdAndType')
            ->withConsecutive(
                [$this->integrationId, ResourceType::Opportunity],
                [$this->technicalContactId, ResourceType::Contact],
                [$this->functionalContactId, ResourceType::Contact],
            )
            ->willReturnOnConsecutiveCalls(
                $insightlyIntegrationMapping,
                $insightlyTechnicalContactMapping,
                $insightlyFunctionalContactMapping,
            );
    }

    private function givenThereIsAnIntegration(UuidInterface $integrationId): Integration
    {
        $integration = new Integration(
            $this->integrationId,
            IntegrationType::SearchApi,
            'My integration',
            'This is my integration',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            []
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        return $integration;
    }
}
