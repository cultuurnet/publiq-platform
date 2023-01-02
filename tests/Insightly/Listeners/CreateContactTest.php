<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateContact;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class CreateContactTest extends TestCase
{
    use MockInsightlyClient;

    private CreateContact $createContact;

    private ContactRepository&MockObject $contactRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    protected function setUp(): void
    {
        $this->contactRepository = $this->createMock(ContactRepository::class);

        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->mockCrmClient();

        $this->createContact = new CreateContact(
            $this->insightlyClient,
            $this->contactRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    /**
     * @test
     */
    public function it_uploads_a_contact(): void
    {
        // Given
        $integrationId = Uuid::uuid4();
        $contactId = Uuid::uuid4();
        $contactType = ContactType::Technical;
        $contactInsightlyId = 985413;
        $integrationInsightlyId = 3333;

        $contact = $this->givenThereIsAContactForAnIntegration($contactId, $integrationId, $contactType);
        $this->givenTheIntegrationIsMappedToInsightly($integrationId, $integrationInsightlyId);

        // Then it stores the contact at Insightly
        $this->contactResource->expects($this->once())
            ->method('create')
            ->with($contact)
            ->willReturn($contactInsightlyId);

        // Then it stores the mapping
        $expectedContactMapping = new InsightlyMapping(
            $contactId,
            $contactInsightlyId,
            ResourceType::Contact
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($expectedContactMapping);

        // Then it links the contact to the integration at Insightly
        $this->opportunityResource->expects($this->once())
            ->method('linkContact')
            ->with($integrationInsightlyId, $contactInsightlyId, $contactType);

        // When
        $this->createContact->handle(new ContactCreated($contact->id));
    }

    /**
     * @test
     */
    public function it_does_not_upload_a_contributor(): void
    {
        $contactId = Uuid::uuid4();
        $this->givenThereIsAContactForAnIntegration($contactId, Uuid::uuid4(), ContactType::Contributor);

        $this->insightlyClient->expects($this->never())
            ->method('contacts');

        $this->createContact->handle(new ContactCreated($contactId));
    }

    private function givenThereIsAContactForAnIntegration(
        UuidInterface $contactId,
        UuidInterface $integrationId,
        ContactType $contactType,
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
            ->method('getById')
            ->with($contact->id)
            ->willReturn($contact);

        return $contact;
    }

    private function givenTheIntegrationIsMappedToInsightly(
        UuidInterface $integrationId,
        int $integrationInsightlyId
    ): void {
        $insightlyIntegrationMapping = new InsightlyMapping(
            $integrationId,
            $integrationInsightlyId,
            ResourceType::Opportunity,
        );

        $this->insightlyMappingRepository->expects(self::once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($insightlyIntegrationMapping);
    }
}
