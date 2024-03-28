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
use App\Insightly\Objects\InsightlyContact;
use App\Insightly\Objects\InsightlyContacts;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
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
    private int $insightlyOpportunityId;
    private UuidInterface $contactId;
    private int $insightlyContactId;
    private int $insightlyProjectId;
    private string $contactEmail;
    private ContactRepository&MockObject $contactRepository;
    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    protected function setUp(): void
    {
        $this->integrationId = Uuid::uuid4();
        $this->insightlyOpportunityId = 111;

        $this->contactId = Uuid::uuid4();
        $this->insightlyContactId = 222;
        $this->contactEmail = 'info@publiq.be';
        $this->insightlyProjectId = 333;

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

    #[DataProvider('provideIntegrationMappingCases')]
    public function test_it_links_a_new_insightly_contact_when_contact_was_created(
        bool $mappedToOpportunity,
        bool $mappedToProject,
    ): void {
        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenOnlyTheIntegrationIsMappedToInsightly($mappedToOpportunity, $mappedToProject);
        $this->givenTheInsightlyContactsFoundByEmailAre([]);

        $this->thenItStoresTheContactAtInsightly($contact, $this->insightlyContactId);

        $this->thenItStoresTheContactMapping($this->contactId, $this->insightlyContactId);

        if ($mappedToOpportunity) {
            $this->thenItLinksTheContactToTheOpportunityAtInsightly(
                $this->insightlyOpportunityId,
                $this->insightlyContactId
            );
        }

        if ($mappedToProject) {
            $this->thenItLinksTheContactToTheProjectInInsightly(
                $this->insightlyProjectId,
                $this->insightlyContactId
            );
        }

        $this->syncContact->handleContactCreated(new ContactCreated($this->contactId));
    }

    #[DataProvider('provideExistingEmailCases')]
    public function test_it_guards_unique_email_in_insightly_when_contact_was_created(
        array $insightlyContacts,
        int $expectedMappedInsightlyContactId
    ): void {
        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenOnlyTheIntegrationIsMappedToInsightly(true, true);
        $this->givenTheInsightlyContactsFoundByEmailAre($insightlyContacts);

        $this->thenItDoesNotStoreAContactAtInsightly();
        $this->thenItUpdatesTheContactAtInsightly($contact, $expectedMappedInsightlyContactId);

        $this->thenItStoresTheContactMapping($this->contactId, $expectedMappedInsightlyContactId);
        $this->thenItLinksTheContactToTheOpportunityAtInsightly(
            $this->insightlyOpportunityId,
            $expectedMappedInsightlyContactId
        );

        $this->thenItLinksTheContactToTheProjectInInsightly(
            $this->insightlyProjectId,
            $expectedMappedInsightlyContactId
        );

        $this->syncContact->handleContactCreated(new ContactCreated($this->contactId));
    }

    public static function provideExistingEmailCases(): Iterator
    {
        yield 'one contact found' => [
            'insightlyContacts' => [
                new InsightlyContact(42, 0),
            ],
            'expectedMappedInsightlyContactId' => 42,
        ];

        yield 'multiple contacts found, single one with most links' => [
            'insightlyContacts' => [
                new InsightlyContact(13, 0),
                new InsightlyContact(42, 1),
                new InsightlyContact(14, 0),
                new InsightlyContact(57, 0),
                new InsightlyContact(63, 0),
            ],
            'expectedMappedInsightlyContacts' => 42, // The one with the most links is chosen
        ];

        yield 'multiple contacts found, multiple with most links' => [
            'insightlyContacts' => [
                new InsightlyContact(13, 0),
                new InsightlyContact(42, 1),
                new InsightlyContact(14, 1),
                new InsightlyContact(57, 0),
                new InsightlyContact(63, 0),
            ],
            'expectedMappedInsightlyContactId' => 14, // The one with the lowest id is chosen when links are equal
        ];
    }

    public function test_it_updates_the_insightly_contact_when_contact_was_updated_with_same_email(): void
    {
        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenTheContactAndIntegrationAreMappedToInsightly(true, true);
        $this->givenTheInsightlyContactsFoundByEmailAre([]);

        $this->thenItDoesNotStoreAContactAtInsightly();

        $this->thenItUpdatesTheContactAtInsightly($contact, $this->insightlyContactId);

        $this->syncContact->handleContactUpdated(new ContactUpdated($this->contactId, false));
    }

    #[DataProvider('provideIntegrationMappingCases')]
    public function test_it_creates_a_new_insightly_contact_when_contact_email_changed(
        bool $mappedToOpportunity,
        bool $mappedToProject,
    ): void {
        $updatedInsightlyContactId = 333;

        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenTheContactAndIntegrationAreMappedToInsightly($mappedToOpportunity, $mappedToProject);
        $this->givenTheInsightlyContactsFoundByEmailAre([]);

        $this->thenItDoesNotUpdateTheOriginalContactAtInsightly($contact, $this->insightlyContactId);

        $this->thenItRemovesTheContactMapping($this->contactId);

        $this->thenItStoresTheContactAtInsightly($contact, $updatedInsightlyContactId);
        $this->thenItStoresTheContactMapping($this->contactId, $updatedInsightlyContactId);

        if ($mappedToOpportunity) {
            $this->thenItRemovesTheContactFromTheOpportunityInInsightly(
                $this->insightlyOpportunityId,
                $this->insightlyContactId
            );
            $this->thenItLinksTheContactToTheOpportunityAtInsightly(
                $this->insightlyOpportunityId,
                $updatedInsightlyContactId
            );
        }

        if ($mappedToProject) {
            $this->thenItRemovesTheContactFromTheProjectInInsightly($this->insightlyProjectId, $this->insightlyContactId);
            $this->thenItLinksTheContactToTheProjectInInsightly(
                $this->insightlyProjectId,
                $updatedInsightlyContactId
            );
        }

        $this->thenItLinksTheNewContactToTheExistingContact($updatedInsightlyContactId, $this->insightlyContactId);

        $this->syncContact->handleContactUpdated(new ContactUpdated($this->contactId, true));
    }

    public static function provideIntegrationMappingCases(): Iterator
    {
        yield 'nothing is mapped' => [
            'mappedToOpportunity' => false,
            'mappedToProject' => false,
        ];

        yield 'project is mapped' => [
            'mappedToOpportunity' => false,
            'mappedToProject' => true,
        ];

        yield 'opportunity is mapped' => [
            'mappedToOpportunity' => true,
            'mappedToProject' => false,
        ];

        yield 'everything is mapped' => [
            'mappedToOpportunity' => true,
            'mappedToProject' => true,
        ];
    }

    #[DataProvider('provideExistingEmailCases')]
    public function test_it_guards_unique_email_in_insightly_when_contact_email_changed(
        array $insightlyContacts,
        int $expectedMappedInsightlyContactId
    ): void {
        $contact = $this->givenThereIsAContactForAnIntegration(ContactType::Functional);
        $this->givenTheContactAndIntegrationAreMappedToInsightly(true, true);
        $this->givenTheInsightlyContactsFoundByEmailAre($insightlyContacts);

        $this->thenItDoesNotStoreAContactAtInsightly();

        $this->thenItRemovesTheContactMapping($this->contactId);
        $this->thenItRemovesTheContactFromTheOpportunityInInsightly($this->insightlyOpportunityId, $this->insightlyContactId);
        $this->thenItRemovesTheContactFromTheProjectInInsightly($this->insightlyProjectId, $this->insightlyContactId);

        $this->thenItStoresTheContactMapping($this->contactId, $expectedMappedInsightlyContactId);
        $this->thenItUpdatesTheContactAtInsightly($contact, $expectedMappedInsightlyContactId);
        $this->thenItLinksTheContactToTheOpportunityAtInsightly(
            $this->insightlyOpportunityId,
            $expectedMappedInsightlyContactId
        );

        $this->thenItLinksTheContactToTheProjectInInsightly(
            $this->insightlyProjectId,
            $expectedMappedInsightlyContactId
        );

        $this->thenItLinksTheNewContactToTheExistingContact($expectedMappedInsightlyContactId, $this->insightlyContactId);

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

    private function givenOnlyTheIntegrationIsMappedToInsightly(
        bool $mappedToOpportunity,
        bool $mappedToProject,
    ): void {
        $insightlyOpportunityMapping = new InsightlyMapping(
            $this->integrationId,
            $this->insightlyOpportunityId,
            ResourceType::Opportunity,
        );

        $insightlyProjectMapping = new InsightlyMapping(
            $this->integrationId,
            $this->insightlyProjectId,
            ResourceType::Project,
        );

        $this->insightlyMappingRepository
            ->method('getByIdAndType')
            ->willReturnCallback(
                fn (UuidInterface $actualIntegrationId, ResourceType $actualResourceType) =>
                    match ([$actualIntegrationId, $actualResourceType]) {
                        [$this->integrationId, ResourceType::Opportunity] => $mappedToOpportunity ? $insightlyOpportunityMapping : throw new ModelNotFoundException(),
                        [$this->integrationId, ResourceType::Project] => $mappedToProject ? $insightlyProjectMapping : throw new ModelNotFoundException(),
                        default => throw new \LogicException('Invalid arguments received'),
                    }
            );
    }

    private function givenTheContactAndIntegrationAreMappedToInsightly(
        bool $mappedToOpportunity,
        bool $mappedToProject,
    ): void {
        $insightlyContactMapping = new InsightlyMapping(
            $this->contactId,
            $this->insightlyContactId,
            ResourceType::Opportunity,
        );

        $insightlyOpportunityMapping = new InsightlyMapping(
            $this->integrationId,
            $this->insightlyOpportunityId,
            ResourceType::Opportunity,
        );

        $insightlyProjectMapping = new InsightlyMapping(
            $this->integrationId,
            $this->insightlyProjectId,
            ResourceType::Project,
        );


        $this->insightlyMappingRepository
            ->method('getByIdAndType')
            ->willReturnCallback(
                fn (UuidInterface $actualIntegrationId, ResourceType $actualResourceType) =>
                    match ([$actualIntegrationId, $actualResourceType]) {
                        [$this->contactId, ResourceType::Contact] => $insightlyContactMapping,
                        [$this->integrationId, ResourceType::Opportunity] => $mappedToOpportunity ? $insightlyOpportunityMapping : throw new ModelNotFoundException(),
                        [$this->integrationId, ResourceType::Project] => $mappedToProject ? $insightlyProjectMapping : throw new ModelNotFoundException(),
                        default => throw new \LogicException('Invalid arguments received'),
                    }
            );
    }

    private function givenTheInsightlyContactsFoundByEmailAre(array $contacts): void
    {
        $this->contactResource
            ->method('findByEmail')
            ->with($this->contactEmail)
            ->willReturn(new InsightlyContacts($contacts));
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

    private function thenItLinksTheContactToTheOpportunityAtInsightly(
        int $insightlyIntegrationId,
        int $insightlyContactId
    ): void {
        $this->opportunityResource->expects($this->once())
            ->method('linkContact')
            ->with($insightlyIntegrationId, $insightlyContactId);
    }

    private function thenItLinksTheNewContactToTheExistingContact(
        int $updatedInsightlyContactId,
        int $oldContactId
    ): void {
        $this->contactResource->expects($this->once())
            ->method('linkContact')
            ->with($updatedInsightlyContactId, $oldContactId);
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

    private function thenItRemovesTheContactFromTheProjectInInsightly(
        int $insightlyProjectId,
        int $insightlyContactId
    ): void {
        $this->projectResource->expects($this->once())
            ->method('unlinkContact')
            ->with($insightlyProjectId, $insightlyContactId);
    }

    private function thenItLinksTheContactToTheProjectInInsightly(
        int $insightlyProjectId,
        int $insightlyContactId
    ): void {
        $this->projectResource->expects($this->once())
            ->method('linkContact')
            ->with($insightlyProjectId, $insightlyContactId);
    }
}
