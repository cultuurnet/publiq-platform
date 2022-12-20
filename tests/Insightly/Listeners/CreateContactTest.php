<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateContact;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Json;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class CreateContactTest extends TestCase
{
    private CreateContact $createContact;

    private ClientInterface&MockObject $client;

    private ContactRepository&MockObject $contactRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);

        $this->contactRepository = $this->createMock(ContactRepository::class);

        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->createContact = new CreateContact(
            new InsightlyClient(
                $this->client,
                'api-key',
                new Pipelines([])
            ),
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
        $this->client->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], Json::encode(['CONTACT_ID' => $insightlyId])),
                new Response(200, [])
            );

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

        $this->client->expects($this->never())
            ->method('sendRequest');

        $this->createContact->handle(new ContactCreated($contact->id));
    }
}
