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
        $this->assertSame('33f1722b-04fc-4652-b99f-2c96de87cf82', $result[0]['id']);
        $this->assertSame('Test', $result[0]['status']);
        $this->assertSame('PERMISSION_33f1722b-04fc-4652-b99f-2c96de87cf82', $result[0]['permissions'][0]['id']);
        $this->assertSame('Label for 33f1722b-04fc-4652-b99f-2c96de87cf82', $result[0]['permissions'][0]['label']);

        // Second result: the demo test organizer
        $this->assertSame('032c7f40-a1c5-4d56-aaf3-52a492262845', $result[1]['id']);
        $this->assertSame('Test', $result[1]['status']);
        $this->assertSame('PERMISSION_032c7f40-a1c5-4d56-aaf3-52a492262845', $result[1]['permissions'][0]['id']);
        $this->assertSame('Label for 032c7f40-a1c5-4d56-aaf3-52a492262845', $result[1]['permissions'][0]['label']);
    }

    public function test_it_handles_missing_keycloak_client(): void
    {
        $result = $this->service->getAndEnrichOrganisations($this->integration);

        $this->assertCount(2, $result);
        // First result: the test UdbOrganizer without permissions
        $this->assertSame([], $result[0]['permissions']);
        $this->assertSame('Test', $result[0]['status']);
    }

    public function test_it_handles_multiple_test_organizers(): void
    {
        $integrationId = Uuid::fromString('7186d084-8a13-47e6-82ec-451c4a314f6e');
        $organizerId1 = new UdbUuid('11111111-1111-1111-1111-111111111111');
        $organizerId2 = new UdbUuid('22222222-2222-2222-2222-222222222222');
        $testClientId1 = Uuid::uuid4();
        $testClientId2 = Uuid::uuid4();

        $integration = (new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Multi Test Integration',
            'Multi Test Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withUdbOrganizers(
            new UdbOrganizer(Uuid::uuid4(), $integrationId, $organizerId1, UdbOrganizerStatus::Pending, $testClientId1),
            new UdbOrganizer(Uuid::uuid4(), $integrationId, $organizerId2, UdbOrganizerStatus::Pending, $testClientId2),
        );

        $testClient1 = new Client($testClientId1, $integrationId, 'test-client-id-1', 'test-secret-1', Environment::Testing);
        $testClient2 = new Client($testClientId2, $integrationId, 'test-client-id-2', 'test-secret-2', Environment::Testing);

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository
            ->method('getById')
            ->willReturnCallback(function (UuidInterface $id) use ($testClientId1, $testClientId2, $testClient1, $testClient2) {
                if ($id->equals($testClientId1)) {
                    return $testClient1;
                }
                if ($id->equals($testClientId2)) {
                    return $testClient2;
                }
                throw new ModelNotFoundException();
            });

        $searchClient = $this->createMock(SearchService::class);
        $searchClient
            ->method('findOrganizers')
            ->willReturn($this->givenMultipleUitpasOrganizers());

        $service = new GetIntegrationOrganizersWithTestOrganizer(
            $searchClient,
            $searchClient,
            $this->uitpasApi,
            $this->contextTest,
            $this->contextProd,
            $keycloakClientRepository
        );

        $result = $service->getAndEnrichOrganisations($integration)->toArray();

        // Should have 2 test organizers + 1 test organizer from config = 3 total
        $this->assertCount(3, $result);
        $this->assertSame('11111111-1111-1111-1111-111111111111', $result[0]['id']);
        $this->assertSame('Test', $result[0]['status']);
        $this->assertSame('22222222-2222-2222-2222-222222222222', $result[1]['id']);
        $this->assertSame('Test', $result[1]['status']);
        $this->assertSame('032c7f40-a1c5-4d56-aaf3-52a492262845', $result[2]['id']);
        $this->assertSame('Test', $result[2]['status']);
    }

    public function test_it_does_not_add_test_organizer_if_already_present(): void
    {
        $integrationId = Uuid::fromString('7186d084-8a13-47e6-82ec-451c4a314f6e');
        $testOrgId = new UdbUuid('032c7f40-a1c5-4d56-aaf3-52a492262845');
        $testClientId = Uuid::uuid4();

        // Create an integration with the test organizer already in the list
        $integration = (new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Integration with Test Org',
            'Integration with Test Org description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withUdbOrganizers(
            new UdbOrganizer(Uuid::uuid4(), $integrationId, $testOrgId, UdbOrganizerStatus::Pending, $testClientId),
        );

        $testClient = new Client($testClientId, $integrationId, 'test-client-id', 'test-secret', Environment::Testing);

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository
            ->method('getById')
            ->willReturnCallback(function (UuidInterface $id) use ($testClientId, $testClient) {
                if ($id->equals($testClientId)) {
                    return $testClient;
                }
                throw new ModelNotFoundException();
            });

        $searchClient = $this->createMock(SearchService::class);
        $searchClient
            ->method('findOrganizers')
            ->willReturn($this->givenTestOrganizerFromConfig());

        $service = new GetIntegrationOrganizersWithTestOrganizer(
            $searchClient,
            $searchClient,
            $this->uitpasApi,
            $this->contextTest,
            $this->contextProd,
            $keycloakClientRepository
        );

        $result = $service->getAndEnrichOrganisations($integration)->toArray();

        // Should only have 1 organizer (the test organizer should not be duplicated)
        $this->assertCount(1, $result);
        $this->assertSame('032c7f40-a1c5-4d56-aaf3-52a492262845', $result[0]['id']);
    }

    public function test_it_handles_mixed_environments(): void
    {
        $integrationId = Uuid::fromString('7186d084-8a13-47e6-82ec-451c4a314f6e');
        $testOrgId = new UdbUuid('11111111-1111-1111-1111-111111111111');
        $prodOrgId = new UdbUuid('22222222-2222-2222-2222-222222222222');
        $testClientId = Uuid::uuid4();
        $prodClientId = Uuid::uuid4();

        $integration = (new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Mixed Environments Integration',
            'Mixed Environments Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withUdbOrganizers(
            new UdbOrganizer(Uuid::uuid4(), $integrationId, $testOrgId, UdbOrganizerStatus::Pending, $testClientId),
            new UdbOrganizer(Uuid::uuid4(), $integrationId, $prodOrgId, UdbOrganizerStatus::Pending, $prodClientId),
        );

        $testClient = new Client($testClientId, $integrationId, 'test-client-id', 'test-secret', Environment::Testing);
        $prodClient = new Client($prodClientId, $integrationId, 'prod-client-id', 'prod-secret', Environment::Production);

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository
            ->method('getById')
            ->willReturnCallback(function (UuidInterface $id) use ($testClientId, $prodClientId, $testClient, $prodClient) {
                if ($id->equals($testClientId)) {
                    return $testClient;
                }
                if ($id->equals($prodClientId)) {
                    return $prodClient;
                }
                throw new ModelNotFoundException();
            });

        $searchClient = $this->createMock(SearchService::class);
        $searchClient
            ->method('findOrganizers')
            ->willReturn($this->givenMixedEnvironmentsOrganizers());

        $service = new GetIntegrationOrganizersWithTestOrganizer(
            $searchClient,
            $searchClient,
            $this->uitpasApi,
            $this->contextTest,
            $this->contextProd,
            $keycloakClientRepository
        );

        $result = $service->getAndEnrichOrganisations($integration)->toArray();

        // Should have 1 test + 1 prod + 1 test organizer from config = 3 total
        $this->assertCount(3, $result);

        // Check test organizers
        $testOrganizers = array_filter($result, fn (array $org) => $org['status'] === 'Test');
        $this->assertCount(2, $testOrganizers);

        // Check production organizers
        $prodOrganizers = array_filter($result, fn (array $org) => $org['status'] === 'Live');
        $this->assertCount(1, $prodOrganizers);
        $this->assertSame('22222222-2222-2222-2222-222222222222', array_values($prodOrganizers)[0]['id']);
    }

    private function givenMultipleUitpasOrganizers(): PagedCollection
    {
        $pagedCollection = new PagedCollection();

        $org1 = new SapiOrganizer();
        $org1->setId('11111111-1111-1111-1111-111111111111');
        $org1->setName(new TranslatedString(['Test Org 1']));

        $org2 = new SapiOrganizer();
        $org2->setId('22222222-2222-2222-2222-222222222222');
        $org2->setName(new TranslatedString(['Test Org 2']));

        $collection = new Collection();
        $collection->setItems([$org1, $org2]);
        $pagedCollection->setMember($collection);
        return $pagedCollection;
    }

    private function givenTestOrganizerFromConfig(): PagedCollection
    {
        $pagedCollection = new PagedCollection();
        $org = new SapiOrganizer();
        $org->setId('032c7f40-a1c5-4d56-aaf3-52a492262845');
        $org->setName(new TranslatedString(['Test Organizer']));
        $collection = new Collection();
        $collection->setItems([$org]);
        $pagedCollection->setMember($collection);
        return $pagedCollection;
    }

    private function givenMixedEnvironmentsOrganizers(): PagedCollection
    {
        $pagedCollection = new PagedCollection();

        $org1 = new SapiOrganizer();
        $org1->setId('11111111-1111-1111-1111-111111111111');
        $org1->setName(new TranslatedString(['Test Org']));

        $org2 = new SapiOrganizer();
        $org2->setId('22222222-2222-2222-2222-222222222222');
        $org2->setName(new TranslatedString(['Prod Org']));

        $collection = new Collection();
        $collection->setItems([$org1, $org2]);
        $pagedCollection->setMember($collection);
        return $pagedCollection;
    }
}
