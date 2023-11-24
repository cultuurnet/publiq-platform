<?php

declare(strict_types=1);

namespace Tests\ProjectAanvraag\Listeners;

use App\Domain\Auth\CurrentUser;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Json;
use App\ProjectAanvraag\Listeners\CreateWidget;
use App\ProjectAanvraag\ProjectAanvraagClient;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\AssertRequest;

final class CreateWidgetTest extends TestCase
{
    use AssertRequest;

    /**
     * @var ClientInterface&MockObject
     */
    private $client;

    /**
     * @var IntegrationRepository&MockObject
     */
    private $integrationRepository;

    /**
     * @var ContactRepository&MockObject
     */
    private $contactRepository;

    /**
     * @var UiTiDv1ConsumerRepository&MockObject
     */
    private $uiTiDv1ConsumerRepository;

    /**
     * @var LoggerInterface&MockObject
     */
    private $logger;

    private CurrentUser $currentUser;

    private CreateWidget $createWidget;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->uiTiDv1ConsumerRepository = $this->createMock(UiTiDv1ConsumerRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $userModel = UserModel::fromSession([
            'id' => 'auth0|6ab8d3ff-018f-4c85-b778-ecc915f2b887',
            'email' => 'an.mock@example.com',
            'name' => 'An Mock',
            'first_name' => 'An',
            'last_name' => 'Mock',
        ]);

        Auth::shouldReceive('user')
            ->andReturn($userModel);
        $this->currentUser = new CurrentUser(App::get(Auth::class));
        dd($this->currentUser);

        $this->createWidget = new CreateWidget(
            new ProjectAanvraagClient(
                $this->client,
                $this->logger
            ),
            $this->integrationRepository,
            $this->contactRepository,
            $this->uiTiDv1ConsumerRepository,
            123,
            $this->logger,
            $this->currentUser
        );
    }

    public function test_it_creates_a_widget(): void
    {
        $integrationId = Uuid::uuid4();
        $integration = new Integration(
            $integrationId,
            IntegrationType::Widgets,
            'My widgets project',
            'This is my widgets project',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $userId = Uuid::uuid4();
        $contact = new Contact(
            $userId,
            $integrationId,
            'john.doe@anonymous.com',
            ContactType::Contributor,
            'John',
            'Doe'
        );

        $testConsumer = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            'consumer-id-testing',
            'consumer-key-testing',
            'consumer-secret-testing',
            'api-key-testing',
            UiTiDv1Environment::Testing
        );
        $productionConsumer = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            'consumer-id-production',
            'consumer-key-production',
            'consumer-secret-production',
            'api-key-production',
            UiTiDv1Environment::Production
        );

        $expectedRequest = new Request(
            'POST',
            'projects',
            [],
            Json::encode([
                'userId' => $userId->toString(),
                'name' => $integration->name,
                'summary' => $integration->description,
                'groupId' => 123,
                'testApiKeySapi3' => 'api-key-testing',
                'liveApiKeySapi3' => 'api-key-production',
            ])
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $this->contactRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn(new Collection([$contact]));

        $this->uiTiDv1ConsumerRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn([$testConsumer, $productionConsumer]);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->createWidget->handleIntegrationCreated(new IntegrationCreated($integrationId));
    }
}
