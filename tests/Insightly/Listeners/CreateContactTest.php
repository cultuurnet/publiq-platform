<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Interfaces\ContactResource;
use App\Insightly\Interfaces\OpportunityResource;
use App\Insightly\Listeners\CreateContact;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class CreateContactTest extends TestCase
{
    private CreateContact $createContact;

    private ContactRepository&MockObject $contactRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private InsightlyClient&MockObject $insightlyClient;

    private ContactResource&MockObject $contactResource;

    private OpportunityResource&MockObject $opportunityResource;

    protected function setUp(): void
    {
        $this->contactRepository = $this->createMock(ContactRepository::class);

        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->insightlyClient = $this->createMock(InsightlyClient::class);
        $this->contactResource = $this->createMock(ContactResource::class);
        $this->opportunityResource = $this->createMock(OpportunityResource::class);
        $this->insightlyClient->expects($this->any())
            ->method('contacts')
            ->willReturn($this->contactResource);
        $this->insightlyClient->expects($this->any())
            ->method('opportunities')
            ->willReturn($this->opportunityResource);

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
        $integrationId = Uuid::uuid4();
        $contactId = Uuid::uuid4();

        $contact = new Contact(
            $contactId,
            $integrationId,
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe'
        );

        $this->contactRepository->expects(self::once())
            ->method('getById')
            ->with($contact->id)
            ->willReturn($contact);

        $insightlyId = 985413;
        $this->contactResource->expects($this->once())
            ->method('create')
            ->willReturn($insightlyId);

        $this->opportunityResource->expects($this->once())
            ->method('linkContact');

        $insightlyIntegrationMapping = new InsightlyMapping(
            $contactId,
            $insightlyId,
            ResourceType::Contact
        );

        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($insightlyIntegrationMapping);

        $this->insightlyMappingRepository->expects(self::once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($insightlyIntegrationMapping);

        $this->createContact->handle(new ContactCreated($contact->id));
    }

    /**
     * @test
     */
    public function it_does_not_upload_a_contributor(): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'jane.doe@anonymous.com',
            ContactType::Contributor,
            'Jane',
            'Doe'
        );

        $this->contactRepository->expects(self::once())
            ->method('getById')
            ->with($contact->id)
            ->willReturn($contact);

        $this->insightlyClient->expects($this->never())
            ->method('contacts');

        $this->createContact->handle(new ContactCreated($contact->id));
    }
}
