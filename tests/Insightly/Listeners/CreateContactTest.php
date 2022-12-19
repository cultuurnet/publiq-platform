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
use App\Insightly\Models\InsightlyMappingModel;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\EloquentInsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Json;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class CreateContactTest extends TestCase
{
    private CreateContact $createContact;

    private ClientInterface&MockObject $client;

    private ContactRepository&MockObject $contactRepository;

    private EloquentInsightlyMappingRepository $insightlyMappingRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(ClientInterface::class);

        $this->contactRepository = $this->createMock(ContactRepository::class);

        $this->insightlyMappingRepository = new EloquentInsightlyMappingRepository();

        $this->createContact = new CreateContact(
            new InsightlyClient(
                $this->client,
                'api-key',
                new Pipelines([])
            ),
            $this->contactRepository,
            $this->insightlyMappingRepository
        );
    }

    /**
     * @test
     */
    public function it_uploads_a_contact(): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe'
        );

        $this->contactRepository->expects(self::once())
            ->method('getById')
            ->with($contact->id)
            ->willReturn($contact);

        $insightlyIntegrationMapping = new InsightlyMapping(
            $contact->integrationId,
            45872,
            ResourceType::Opportunity
        );
        $this->insightlyMappingRepository->save($insightlyIntegrationMapping);

        $this->client->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], Json::encode(['CONTACT_ID' => 985413])),
                new Response(200, [])
            );

        $this->createContact->handle(new ContactCreated($contact->id));

        $this->assertDatabaseHas(InsightlyMappingModel::class, [
            'id' => $contact->id->toString(),
            'resource_type' => 'contact',
        ]);
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
