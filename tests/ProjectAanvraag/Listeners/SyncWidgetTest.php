<?php

declare(strict_types=1);

namespace Tests\ProjectAanvraag\Listeners;

use App\Auth0\Repositories\Auth0UserRepository;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Json;
use App\ProjectAanvraag\Listeners\SyncWidget;
use App\ProjectAanvraag\ProjectAanvraagClient;
use App\ProjectAanvraag\ProjectAanvraagUrl;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\AssertRequest;
use Tests\TestCase;

final class SyncWidgetTest extends TestCase
{
    use AssertRequest;

    private ClientInterface&MockObject $client;

    private IntegrationRepository&MockObject $integrationRepository;

    private ContactRepository&MockObject $contactRepository;

    private UiTiDv1ConsumerRepository&MockObject $uiTiDv1ConsumerRepository;

    private Auth0UserRepository&MockObject $auth0UserRepository;

    private SyncWidget $syncWidget;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(ClientInterface::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->uiTiDv1ConsumerRepository = $this->createMock(UiTiDv1ConsumerRepository::class);
        $this->auth0UserRepository = $this->createMock(Auth0UserRepository::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->syncWidget = new SyncWidget(
            new ProjectAanvraagClient(
                $logger,
                $this->client
            ),
            $this->integrationRepository,
            $this->contactRepository,
            $this->uiTiDv1ConsumerRepository,
            123,
            $this->auth0UserRepository,
            $logger
        );
    }

    public function test_it_handles_integration_created(): void
    {
        $integration = $this->givenThereIsAnIntegration(IntegrationStatus::PendingApprovalIntegration);

        $this->givenThereIsAContact($integration->id);

        $this->givenThereAreConsumers($integration->id);

        $this->assertRequest($integration, 'application_sent');

        $this->syncWidget->handleIntegrationCreated(new IntegrationCreated($integration->id));
    }

    public function test_it_handles_integration_activated(): void
    {
        $integration = $this->givenThereIsAnIntegration(IntegrationStatus::Active);

        $this->givenThereIsAContact($integration->id);

        $this->givenThereAreConsumers($integration->id);

        $this->assertRequest($integration, 'active');

        $this->syncWidget->handleIntegrationActivated(new IntegrationActivated($integration->id));
    }

    public function test_it_handles_integration_blocked(): void
    {
        $integration = $this->givenThereIsAnIntegration(IntegrationStatus::Blocked);

        $this->givenThereIsAContact($integration->id);

        $this->givenThereAreConsumers($integration->id);

        $this->assertRequest($integration, 'blocked');

        $this->syncWidget->handleIntegrationBlocked(new IntegrationBlocked($integration->id));
    }

    public function test_it_handles_integration_deleted(): void
    {
        $integration = $this->givenThereIsAnIntegration(IntegrationStatus::Deleted);

        $this->givenThereIsAContact($integration->id);

        $this->givenThereAreConsumers($integration->id);

        $this->assertRequest($integration, 'blocked');

        $this->syncWidget->handleIntegrationDeleted(new IntegrationDeleted($integration->id));
    }

    public function test_it_handles_integration_updated(): void
    {
        $integration = $this->givenThereIsAnIntegration(IntegrationStatus::Active);

        $this->givenThereIsAContact($integration->id);

        $this->givenThereAreConsumers($integration->id);

        $this->assertRequest($integration, 'active');

        $this->syncWidget->handleIntegrationUpdated(new IntegrationUpdated($integration->id));
    }

    private function givenThereIsAnIntegration(IntegrationStatus $integrationStatus): Integration
    {
        $integrationId = Uuid::uuid4();

        $integration = new Integration(
            $integrationId,
            IntegrationType::Widgets,
            'My widgets project',
            'This is my widgets project',
            Uuid::uuid4(),
            $integrationStatus,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        return $integration;
    }

    private function givenThereIsAContact(UuidInterface $integrationId): Contact
    {
        $contact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            'john.doe@anonymous.com',
            ContactType::Contributor,
            'John',
            'Doe'
        );

        $this->contactRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn(new Collection([$contact]));

        $this->auth0UserRepository->expects($this->once())
            ->method('findUserIdByEmail')
            ->with($contact->email)
            ->willReturn('google-oauth2|102486314601596809843');

        return $contact;
    }

    private function givenThereAreConsumers(UuidInterface $integrationId): void
    {
        $testConsumer = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            'consumer-id-testing',
            'consumer-key-testing',
            'api-key-testing',
            UiTiDv1Environment::Testing
        );

        $productionConsumer = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            'consumer-id-production',
            'consumer-key-production',
            'api-key-production',
            UiTiDv1Environment::Production
        );

        $this->uiTiDv1ConsumerRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn([$testConsumer, $productionConsumer]);
    }

    private function assertRequest(Integration $integration, string $state): void
    {
        $expectedRequest = new Request(
            'POST',
            ProjectAanvraagUrl::getBaseUri() . '/projects',
            [],
            Json::encode([
                'userId' => 'google-oauth2|102486314601596809843',
                'name' => $integration->name,
                'summary' => $integration->description,
                'groupId' => 123,
                'testApiKeySapi3' => 'api-key-testing',
                'liveApiKeySapi3' => 'api-key-production',
                'state' => $state,
            ])
        );

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));
    }
}
