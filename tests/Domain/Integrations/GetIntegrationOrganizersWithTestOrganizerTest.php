<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\GetIntegrationOrganizersWithTestOrganizer;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Search\Sapi3\SearchService;
use App\UiTPAS\Dto\UiTPASPermission;
use App\UiTPAS\Dto\UiTPASPermissionDetail;
use App\UiTPAS\Dto\UiTPASPermissionDetails;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use CultuurNet\SearchV3\ValueObjects\Collection;
use CultuurNet\SearchV3\ValueObjects\Organizer as SapiOrganizer;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\TestCase;

final class GetIntegrationOrganizersWithTestOrganizerTest extends TestCase
{
    private UiTPASApiInterface&MockObject $uitpasApi;
    private GetIntegrationOrganizersWithTestOrganizer $service;
    private Integration $integration;
    private ClientCredentialsContext $contextTest;
    private ClientCredentialsContext $contextProd;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set(UiTPASConfig::TEST_ORGANISATION->value, '032c7f40-a1c5-4d56-aaf3-52a492262845');

        $this->contextTest = new ClientCredentialsContext(
            Environment::Testing,
            'https://test.publiq.be/',
            '123',
            'secret',
            'uitid'
        );

        $this->contextProd = new ClientCredentialsContext(
            Environment::Production,
            'https://publiq.be/',
            '456',
            'geheimpje',
            'uitid'
        );

        $searchClient = $this->createMock(SearchService::class);
        $searchClient
            ->method('findOrganizers')
            ->willReturn(
                $this->givenUitpasOrganizers()
            );

        $this->uitpasApi = $this->createMock(UiTPASApiInterface::class);
        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);

        $this->service = new GetIntegrationOrganizersWithTestOrganizer(
            $searchClient,
            $this->uitpasApi,
            $this->contextTest,
            $this->contextProd,
            $keycloakClientRepository
        );

        $integrationId = Uuid::fromString('7186d084-8a13-47e6-82ec-451c4a314f6e');
        $organizerId = new UdbUuid('33f1722b-04fc-4652-b99f-2c96de87cf82');
        $subscriptionId = Uuid::fromString('90366a07-62c1-40ef-bcd4-84c583d2fac3');
        $testClientId = Uuid::uuid4();

        $this->integration = (new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            $subscriptionId,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withUdbOrganizers(
            new UdbOrganizer(Uuid::uuid4(), $integrationId, $organizerId, UdbOrganizerStatus::Pending, $testClientId),
        );

        // Mock keycloak client repository to return a test environment client
        $testClient = new Client($testClientId, $integrationId, 'test-client-id', 'test-secret', Environment::Testing);
        $keycloakClientRepository
            ->method('getById')
            ->willReturnCallback(function (UuidInterface $id) use ($testClientId, $testClient) {
                if ($id->equals($testClientId)) {
                    return $testClient;
                }
                throw new ModelNotFoundException();
            });
    }

    private function givenUitpasOrganizers(): PagedCollection
    {
        $pagedCollection = new PagedCollection();
        $org = new SapiOrganizer();
        $org->setId('33f1722b-04fc-4652-b99f-2c96de87cf82');
        $org->setName(new TranslatedString(['Test Org']));
        $collection = new Collection();
        $collection->setItems([$org]);
        $pagedCollection->setMember($collection);
        return $pagedCollection;
    }

    public function test_it_enriches_organizers_with_permissions(): void
    {
        $this->integration = $this->integration->withKeycloakClients(
            new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-id-1', Environment::Testing),
            new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-id-1', Environment::Production)
        );

        $this->uitpasApi
            ->method('fetchPermissions')
            ->willReturnCallback(function (ClientCredentialsContext $context, UdbUuid $organizerId) {
                return new UiTPASPermission(
                    $organizerId,
                    'organizer-' . $organizerId,
                    new UiTPASPermissionDetails([new UiTPASPermissionDetail('PERMISSION_' . $organizerId, 'label for ' . $organizerId)])
                );
            });

        $result = $this->service->getAndEnrichOrganisations($this->integration)->toArray();

        $this->assertCount(2, $result);
        // First result: the test UdbOrganizer
        $this->assertSame('032c7f40-a1c5-4d56-aaf3-52a492262845', $result[0]['id']);
        $this->assertSame('Test', $result[0]['status']);
        $this->assertSame('PERMISSION_032c7f40-a1c5-4d56-aaf3-52a492262845', $result[0]['permissions'][0]['id']);
        $this->assertSame('Label for 032c7f40-a1c5-4d56-aaf3-52a492262845', $result[0]['permissions'][0]['label']);

        // Second result: the demo test organizer
        $this->assertSame('33f1722b-04fc-4652-b99f-2c96de87cf82', $result[1]['id']);
        $this->assertSame('Live', $result[1]['status']);
        $this->assertSame('PERMISSION_33f1722b-04fc-4652-b99f-2c96de87cf82', $result[1]['permissions'][0]['id']);
        $this->assertSame('Label for 33f1722b-04fc-4652-b99f-2c96de87cf82', $result[1]['permissions'][0]['label']);
    }

    public function test_it_handles_missing_keycloak_client(): void
    {
        $result = $this->service->getAndEnrichOrganisations($this->integration);

        $this->assertCount(2, $result);
        // First result: the test UdbOrganizer without permissions
        $this->assertSame([], $result[0]['permissions']);
        $this->assertSame('Test', $result[0]['status']);
    }
}
