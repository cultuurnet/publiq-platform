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
    private UuidInterface $integrationId;
    private int $insightlyOpportunityId;
    private UuidInterface $contactId;
    private int $insightlyContactId;

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->integrationId = Uuid::uuid4();
        $this->insightlyOpportunityId = 111;

        $this->contactId = Uuid::uuid4();
        $this->insightlyContactId = 222;

        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->contactId = Uuid::uuid4();

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
        $this->givenThereIsADeletedContact($contactType);
        $this->givenTheContactAndIntegrationAreMappedToInsightly();

        $this->opportunityResource->expects($this->once())
            ->method('unlinkContact')
            ->with($this->insightlyOpportunityId, $this->insightlyContactId);

        $event = new ContactDeleted($this->contactId);
        $this->deleteContact->handle($event);
    }

    public function test_it_does_not_try_to_unlink_a_contributor(): void
    {
        $this->givenThereIsADeletedContact(ContactType::Contributor);

        $this->opportunityResource->expects($this->never())
            ->method('unlinkContact');

        $event = new ContactDeleted($this->contactId);
        $this->deleteContact->handle($event);
    }

    public function test_it_does_not_throw_exception_when_ContactCannotBeUnlinked_is_thrown(): void
    {
        $this->givenThereIsADeletedContact(ContactType::Functional);
        $this->givenTheContactAndIntegrationAreMappedToInsightly();

        $this->opportunityResource->expects($this->once())
            ->method('unlinkContact')
            ->with($this->insightlyOpportunityId, $this->insightlyContactId)
            ->willThrowException(new ContactCannotBeUnlinked());

        $event = new ContactDeleted($this->contactId);
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

    private function givenThereIsADeletedContact(ContactType $contactType): Contact
    {
        $contact = new Contact(
            $this->contactId,
            $this->integrationId,
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

    private function givenTheContactAndIntegrationAreMappedToInsightly(): void
    {
        $insightlyContactMapping = new InsightlyMapping(
            $this->contactId,
            $this->insightlyContactId,
            ResourceType::Contact,
        );

        $insightlyOpportunityMapping = new InsightlyMapping(
            $this->integrationId,
            $this->insightlyOpportunityId,
            ResourceType::Opportunity,
        );

        $this->insightlyMappingRepository
            ->method('getByIdAndType')
            ->will($this->returnValueMap([
                [$this->contactId, ResourceType::Contact, $insightlyContactMapping],
                [$this->integrationId, ResourceType::Opportunity, $insightlyOpportunityMapping],
            ]));
    }
}
