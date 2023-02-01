<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\SyncContact;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Iterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class SyncContactTest extends TestCase
{
    use MockInsightlyClient;

    private SyncContact $syncContact;
    private UuidInterface $integrationId;
    private int $insightlyIntegrationId;
    private UuidInterface $contactId;
    private int $insightlyContactId;
    private string $contactEmail;
    private ContactRepository&MockObject $contactRepository;
    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    protected function setUp(): void
    {
        $this->integrationId = Uuid::uuid4();
        $this->insightlyIntegrationId = 111;

        $this->contactId = Uuid::uuid4();
        $this->insightlyContactId = 222;
        $this->contactEmail = 'info@publiq.be';

        $this->mockCrmClient();
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->syncContact = new SyncContact(
            $this->insightlyClient,
            $this->contactRepository,
            $this->insightlyMappingRepository,
            new NullLogger(),
        );
    }

    public function test_it_does_not_create_a_contributor(): void
    {
        $this->givenThereIsAContactForAnIntegration(ContactType::Contributor);

        $this->insightlyClient->expects($this->never())
            ->method('contacts');

        $this->syncContact->handleContactCreated(new ContactCreated($this->contactId));
    }

    public function test_it_does_not_update_a_contributor(): void
    {
        $this->givenThereIsAContactForAnIntegration(ContactType::Contributor);

        $this->insightlyClient->expects($this->never())
            ->method('contacts');

        $this->syncContact->handleContactUpdated(new ContactUpdated($this->contactId, true));
    }

    public function test_it_links_a_new_insightly_contact_when_platform_contact_was_created(): void
    {
        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenOnlyTheIntegrationIsMappedToInsightly();
        $this->givenTheInsightlyContactsFoundByEmailAre([]);

        $this->thenItStoresTheContactAtInsightly($contact, $this->insightlyContactId);

        $this->thenItStoresTheContactMapping($this->contactId, $this->insightlyContactId);

        $this->thenItLinksTheContactToTheIntegrationAtInsightly(
            $this->insightlyIntegrationId,
            $this->insightlyContactId,
            ContactType::Functional
        );

        $this->syncContact->handleContactCreated(new ContactCreated($this->contactId));
    }

    /**
     * @dataProvider provideExistingEmailCases
     */
    public function test_it_guards_unique_email_in_insightly_when_platform_contact_was_created(
        array $insightlyContactIds,
        int $expectedMappedInsightlyContactId
    ): void {
        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenOnlyTheIntegrationIsMappedToInsightly();
        $this->givenTheInsightlyContactsFoundByEmailAre($insightlyContactIds);

        $this->thenItDoesNotStoreAContactAtInsightly();
        $this->thenItUpdatesTheContactAtInsightly($contact, $expectedMappedInsightlyContactId);

        $this->thenItStoresTheContactMapping($this->contactId, $expectedMappedInsightlyContactId);
        $this->thenItLinksTheContactToTheIntegrationAtInsightly(
            $this->insightlyIntegrationId,
            $expectedMappedInsightlyContactId,
            ContactType::Functional,
        );

        $this->syncContact->handleContactCreated(new ContactCreated($this->contactId));
    }

    public function provideExistingEmailCases(): Iterator
    {
        yield 'one contact found' => [
            'insightlyContactIds' => [42],
            'expectedMappedInsightlyContactId' => 42,
        ];

        yield 'multiple contacts found' => [
            'insightlyContactIds' => [52, 136, 68, 42, 124, 88, 99],
            'expectedMappedInsightlyContactId' => 42, // The lowest id is chosen
        ];
    }

    public function test_it_updates_the_insightly_contact_when_platform_contact_was_updated_with_same_email(): void
    {
        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenTheContactAndIntegrationAreMappedToInsightly();
        $this->givenTheInsightlyContactsFoundByEmailAre([]);

        $this->thenItDoesNotStoreAContactAtInsightly();

        $this->thenItUpdatesTheContactAtInsightly($contact, $this->insightlyContactId);

        $this->syncContact->handleContactUpdated(new ContactUpdated($this->contactId, false));
    }

    public function test_it_creates_a_new_insightly_contact_when_platform_contact_email_changed(): void
    {
        $updatedInsightlyContactId = 333;

        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenTheContactAndIntegrationAreMappedToInsightly();
        $this->givenTheInsightlyContactsFoundByEmailAre([]);

        $this->thenItDoesNotUpdateTheOriginalContactAtInsightly($contact, $this->insightlyContactId);

        $this->thenItRemovesTheContactMapping($this->contactId);
        $this->thenItRemovesTheContactFromTheOpportunityInInsightly($this->insightlyIntegrationId, $this->insightlyContactId);

        $this->thenItStoresTheContactAtInsightly($contact, $updatedInsightlyContactId);
        $this->thenItStoresTheContactMapping($this->contactId, $updatedInsightlyContactId);
        $this->thenItLinksTheContactToTheIntegrationAtInsightly(
            $this->insightlyIntegrationId,
            $updatedInsightlyContactId,
            ContactType::Functional
        );

        $this->syncContact->handleContactUpdated(new ContactUpdated($this->contactId, true));
    }

    /**
     * @dataProvider provideExistingEmailCases
     */
    public function test_it_guards_unique_email_in_insightly_when_platform_contact_email_changed(
        array $insightlyContactIds,
        int $expectedMappedInsightlyContactId
    ): void {
        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenTheContactAndIntegrationAreMappedToInsightly();
        $this->givenTheInsightlyContactsFoundByEmailAre($insightlyContactIds);

        $this->thenItDoesNotStoreAContactAtInsightly();

        $this->thenItRemovesTheContactMapping($this->contactId);
        $this->thenItRemovesTheContactFromTheOpportunityInInsightly($this->insightlyIntegrationId, $this->insightlyContactId);

        $this->thenItStoresTheContactMapping($this->contactId, $expectedMappedInsightlyContactId);
        $this->thenItUpdatesTheContactAtInsightly($contact, $expectedMappedInsightlyContactId);
        $this->thenItLinksTheContactToTheIntegrationAtInsightly(
            $this->insightlyIntegrationId,
            $expectedMappedInsightlyContactId,
            ContactType::Functional
        );

        $this->syncContact->handleContactUpdated(new ContactUpdated($this->contactId, true));
    }

    private function givenThereIsAContactForAnIntegration(ContactType $contactType): Contact
    {
        $contact = new Contact(
            $this->contactId,
            $this->integrationId,
            $this->contactEmail,
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

    private function givenOnlyTheIntegrationIsMappedToInsightly(): void
    {
        $insightlyIntegrationMapping = new InsightlyMapping(
            $this->integrationId,
            $this->insightlyIntegrationId,
            ResourceType::Opportunity,
        );

        $this->insightlyMappingRepository->expects($this->once())
            ->method('getByIdAndType')
            ->with($this->integrationId, ResourceType::Opportunity)
            ->willReturnOnConsecutiveCalls($insightlyIntegrationMapping);
    }

    private function givenTheContactAndIntegrationAreMappedToInsightly(): void
    {
        $insightlyContactMapping = new InsightlyMapping(
            $this->contactId,
            $this->insightlyContactId,
            ResourceType::Opportunity,
        );

        $insightlyIntegrationMapping = new InsightlyMapping(
            $this->integrationId,
            $this->insightlyIntegrationId,
            ResourceType::Opportunity,
        );

        $this->insightlyMappingRepository->expects($this->any())
            ->method('getByIdAndType')
            ->withConsecutive(
                [$this->contactId, ResourceType::Contact],
                [$this->integrationId, ResourceType::Opportunity],
                [$this->integrationId, ResourceType::Opportunity],
            )
            ->willReturnOnConsecutiveCalls(
                $insightlyContactMapping,
                $insightlyIntegrationMapping,
                $insightlyIntegrationMapping,
            );
    }

    private function givenTheInsightlyContactsFoundByEmailAre(array $contactIds): void
    {
        $this->contactResource->expects($this->any())
            ->method('findIdsByEmail')
            ->with($this->contactEmail)
            ->willReturn($contactIds);
    }

    private function thenItStoresTheContactAtInsightly(Contact $contact, int $insightlyContactId): void
    {
        $this->contactResource->expects($this->once())
            ->method('create')
            ->with($contact)
            ->willReturn($insightlyContactId);
    }

    private function thenItStoresTheContactMapping(UuidInterface $contactId, int $insightlyContactId): void
    {
        $expectedContactMapping = new InsightlyMapping(
            $contactId,
            $insightlyContactId,
            ResourceType::Contact
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($expectedContactMapping);
    }

    private function thenItLinksTheContactToTheIntegrationAtInsightly(
        int $insightlyIntegrationId,
        int $insightlyContactId,
        ContactType $contactType
    ): void {
        $this->opportunityResource->expects($this->once())
            ->method('linkContact')
            ->with($insightlyIntegrationId, $insightlyContactId, $contactType);
    }

    private function thenItDoesNotStoreAContactAtInsightly(): void
    {
        $this->contactResource->expects($this->never())
            ->method('create');
    }

    private function thenItUpdatesTheContactAtInsightly(Contact $contact, int $insightlyContactId): void
    {
        $this->contactResource->expects($this->once())
            ->method('update')
            ->with($contact, $insightlyContactId);
    }

    private function thenItDoesNotUpdateTheOriginalContactAtInsightly(Contact $contact, int $insightlyContactId): void
    {
        $this->contactResource->expects($this->never())
            ->method('update')
            ->with($contact, $insightlyContactId);
    }

    private function thenItRemovesTheContactMapping(UuidInterface $contactId): void
    {
        $this->insightlyMappingRepository->expects($this->once())
            ->method('deleteById')
            ->with($contactId);
    }

    private function thenItRemovesTheContactFromTheOpportunityInInsightly(
        int $insightlyIntegrationId,
        int $insightlyContactId
    ): void {
        $this->opportunityResource->expects($this->once())
            ->method('unlinkContact')
            ->with($insightlyIntegrationId, $insightlyContactId);
    }
}
