<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactDeleted;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\UnlinkContact;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Iterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class UnlinkContactTest extends TestCase
{
    use MockInsightlyClient;

    private UnlinkContact $deleteContact;

    private ContactRepository&MockObject $contactRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->deleteContact = new UnlinkContact(
            $this->insightlyClient,
            $this->contactRepository,
            $this->insightlyMappingRepository,
            new NullLogger(),
        );
    }

    /**
     * @dataProvider provideUnlinkContactCases
     */
    public function test_it_unlinks_a_contact_person(ContactType $contactType): void
    {
        $contactId = Uuid::uuid4();
        $contactInsightlyId = 42;
        $integrationId = Uuid::uuid4();
        $integrationInsightlyId = 53;

        $this->givenThereIsADeletedContact($contactId, $contactType, $integrationId);
        $this->givenTheContactAndIntegrationAreMappedToInsightly(
            $contactId,
            $contactInsightlyId,
            $integrationId,
            $integrationInsightlyId,
        );

        $this->opportunityResource->expects($this->once())
            ->method('unlinkContact')
            ->with($integrationInsightlyId, $contactInsightlyId);

        $event = new ContactDeleted($contactId);
        $this->deleteContact->handle($event);
    }

    public function test_it_does_not_try_to_unlink_a_contributor(): void
    {
        $contactId = Uuid::uuid4();
        $integrationId = Uuid::uuid4();

        $this->givenThereIsADeletedContact($contactId, ContactType::Contributor, $integrationId);

        $this->opportunityResource->expects($this->never())
            ->method('unlinkContact');

        $event = new ContactDeleted($contactId);
        $this->deleteContact->handle($event);
    }

    public function test_it_does_not_throw_exception_when_ContactCannotBeUnlinked_is_thrown(): void
    {
        $contactId = Uuid::uuid4();
        $contactInsightlyId = 42;
        $integrationId = Uuid::uuid4();
        $integrationInsightlyId = 53;

        $this->givenThereIsADeletedContact($contactId, ContactType::Functional, $integrationId);
        $this->givenTheContactAndIntegrationAreMappedToInsightly(
            $contactId,
            $contactInsightlyId,
            $integrationId,
            $integrationInsightlyId,
        );

        $this->opportunityResource->expects($this->once())
            ->method('unlinkContact')
            ->with($integrationInsightlyId, $contactInsightlyId)
            ->willThrowException(new ContactCannotBeUnlinked());

        $event = new ContactDeleted($contactId);
        $this->deleteContact->handle($event);
    }

    public function provideUnlinkContactCases(): Iterator
    {
        yield 'functional' => [
            'contactType' => ContactType::Functional,
        ];

        yield 'technical' => [
            'contactType' => ContactType::Technical,
        ];
    }

    private function givenThereIsADeletedContact(
        UuidInterface $contactId,
        ContactType $contactType,
        UuidInterface $integrationId
    ): Contact {
        $contact = new Contact(
            $contactId,
            $integrationId,
            'jane.doe@anonymous.com',
            $contactType,
            'Jane',
            'Doe'
        );

        $this->contactRepository->expects(self::once())
            ->method('getDeletedById')
            ->with($contact->id)
            ->willReturn($contact);

        return $contact;
    }

    private function givenTheContactAndIntegrationAreMappedToInsightly(
        UuidInterface $contactId,
        int $contactInsightlyId,
        UuidInterface $integrationId,
        int $integrationInsightlyId
    ): void {
        $insightlyContactMapping = new InsightlyMapping(
            $contactId,
            $contactInsightlyId,
            ResourceType::Contact,
        );

        $insightlyIntegrationMapping = new InsightlyMapping(
            $integrationId,
            $integrationInsightlyId,
            ResourceType::Opportunity,
        );

        $this->insightlyMappingRepository->expects(self::exactly(2))
            ->method('getById')
            ->withConsecutive([$contactId], [$integrationId])
            ->willReturn($insightlyContactMapping, $insightlyIntegrationMapping);
    }
}
