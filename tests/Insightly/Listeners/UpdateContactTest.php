<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\ContactLink;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\UpdateContact;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class UpdateContactTest extends TestCase
{
    use MockInsightlyClient;

    private UpdateContact $updateContact;

    private ContactRepository&MockObject $contactRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private ContactLink&MockObject $contactLink;

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);
        $this->contactLink = $this->createMock(ContactLink::class);

        $this->updateContact = new UpdateContact(
            $this->insightlyClient,
            $this->contactLink,
            $this->contactRepository,
            $this->insightlyMappingRepository,
            new NullLogger(),
        );
    }

    public function test_it_updates_a_contact_when_the_email_stayed_the_same(): void
    {
        $contactId = Uuid::uuid4();
        $insightlyId = 42;

        $contact = $this->givenThereIsAContact($contactId, ContactType::Technical);
        $this->givenTheContactAndIntegrationAreMappedToInsightly(
            $contactId,
            $insightlyId,
            $contact->integrationId,
            53
        );

        $this->contactResource->expects($this->once())
            ->method('update')
            ->with($contact, $insightlyId);

        $event = new ContactUpdated($contactId, false);
        $this->updateContact->handle($event);
    }

    public function test_it_links_the_contact_again_when_the_email_changed(): void
    {
        $contactId = Uuid::uuid4();
        $oldInsightlyContactId = 42;
        $newInsightlyContactId = 53;
        $insightlyOpportunityId = 64;

        $contact = $this->givenThereIsAContact($contactId, ContactType::Technical);

        $this->givenTheContactAndIntegrationAreMappedToInsightly(
            $contactId,
            $oldInsightlyContactId,
            $contact->integrationId,
            $insightlyOpportunityId
        );

        // It leaves the old contact as is,
        $this->contactResource->expects($this->never())
            ->method('update');

        // and removes the mapping,
        $this->insightlyMappingRepository->expects($this->once())
            ->method('deleteById')
            ->with($contactId);

        // and removes the link to the opportunity.
        $this->opportunityResource->expects($this->once())
            ->method('unlinkContact')
            ->with($insightlyOpportunityId, $oldInsightlyContactId);

        // It links another contact at Insightly,
        $this->contactLink->expects($this->once())
            ->method('link')
            ->with($contact)
            ->willReturn($newInsightlyContactId);

        // and saves the mapping,
        $expectedContactMapping = new InsightlyMapping(
            $contactId,
            $newInsightlyContactId,
            ResourceType::Contact
        );
        $this->insightlyMappingRepository->expects($this->once())
            ->method('save')
            ->with($expectedContactMapping);

        // and links the contact to the opportunity.
        $this->opportunityResource->expects($this->once())
            ->method('linkContact')
            ->with($insightlyOpportunityId, $newInsightlyContactId);

        $event = new ContactUpdated($contactId, true);
        $this->updateContact->handle($event);
    }

    /**
     * @test
     */
    public function it_does_not_update_a_contributor(): void
    {
        $contactId = Uuid::uuid4();

        $this->givenThereIsAContact($contactId, ContactType::Contributor);

        $this->contactResource->expects($this->never())
            ->method('update');

        $event = new ContactUpdated($contactId, false);
        $this->updateContact->handle($event);
    }

    private function givenThereIsAContact(UuidInterface $contactId, ContactType $contactType): Contact
    {
        $contact = new Contact(
            $contactId,
            Uuid::uuid4(),
            'jane.doe@anonymous.com',
            $contactType,
            'Jane',
            'Doe'
        );

        $this->contactRepository->expects(self::once())
            ->method('getById')
            ->with($contact->id)
            ->willReturn($contact);

        return $contact;
    }

    private function givenTheContactAndIntegrationAreMappedToInsightly(
        UuidInterface $contactId,
        int $insightlyContactId,
        UuidInterface $integrationId,
        int $insightlyOpportunityId,
    ): void {
        $insightlyContactMapping = new InsightlyMapping(
            $contactId,
            $insightlyContactId,
            ResourceType::Contact,
        );

        $insightlyIntegrationMapping = new InsightlyMapping(
            $integrationId,
            $insightlyOpportunityId,
            ResourceType::Opportunity,
        );

        $this->insightlyMappingRepository->expects(self::atMost(2))
            ->method('getByIdAndType')
            ->withConsecutive(
                [$contactId, ResourceType::Contact],
                [$integrationId, ResourceType::Opportunity],
            )
            ->willReturnOnConsecutiveCalls($insightlyContactMapping, $insightlyIntegrationMapping);
    }
}
