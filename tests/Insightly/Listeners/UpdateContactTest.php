<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Contacts\Repositories\ContactRepository;
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

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->updateContact = new UpdateContact(
            $this->insightlyClient,
            $this->contactRepository,
            $this->insightlyMappingRepository,
            new NullLogger(),
        );
    }

    /**
     * @test
     */
    public function it_updates_a_contact(): void
    {
        $contactId = Uuid::uuid4();
        $insightlyId = 42;

        $contact = $this->givenThereIsAContact($contactId, ContactType::Technical);
        $this->givenTheContactIsMappedToInsightly($contactId, $insightlyId);

        $this->contactResource->expects($this->once())
            ->method('update')
            ->with($contact, $insightlyId);

        $event = new ContactUpdated($contactId, false);
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
            ->method('getByIdAndType')
            ->with($contactId, ResourceType::Contact)
            ->willReturn($insightlyIntegrationMapping);
    }
}
