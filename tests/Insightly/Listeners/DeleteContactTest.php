<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactDeleted;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\DeleteContact;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Iterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class DeleteContactTest extends TestCase
{
    use MockInsightlyClient;

    private DeleteContact $deleteContact;

    private ContactRepository&MockObject $contactRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->deleteContact = new DeleteContact(
            $this->insightlyClient,
            $this->contactRepository,
            $this->insightlyMappingRepository,
            new NullLogger(),
        );
    }

    /**
     * @dataProvider provideDeleteContactCases
     */
    public function test_it_deletes_a_contact_person(ContactType $contactType): void
    {
        $contactId = Uuid::uuid4();
        $insightlyId = 42;

        $this->givenThereIsAContact($contactId, $contactType);
        $this->givenTheContactIsMappedToInsightly($contactId, $insightlyId);

        $this->contactResource->expects($this->once())
            ->method('delete')
            ->with($insightlyId);

        $this->insightlyMappingRepository->expects($this->once())
            ->method('deleteById')
            ->with($contactId);

        $event = new ContactDeleted($contactId);
        $this->deleteContact->handle($event);
    }

    public function test_it_does_not_try_to_delete_a_contributor(): void
    {
        $contactId = Uuid::uuid4();

        $this->givenThereIsAContact($contactId, ContactType::Contributor);

        $this->contactResource->expects($this->never())
            ->method('delete');

        $event = new ContactDeleted($contactId);
        $this->deleteContact->handle($event);
    }

    public function provideDeleteContactCases(): Iterator
    {
        yield 'functional' => [
            'contactType' => ContactType::Functional,
        ];

        yield 'technical' => [
            'contactType' => ContactType::Technical,
        ];
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

    private function givenTheContactIsMappedToInsightly(
        UuidInterface $contactId,
        int $contactInsightlyId
    ): void {
        $insightlyIntegrationMapping = new InsightlyMapping(
            $contactId,
            $contactInsightlyId,
            ResourceType::Contact,
        );

        $this->insightlyMappingRepository->expects(self::once())
            ->method('getById')
            ->with($contactId)
            ->willReturn($insightlyIntegrationMapping);
    }
}
