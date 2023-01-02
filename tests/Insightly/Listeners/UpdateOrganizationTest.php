<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\UpdateOrganization;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Json;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class UpdateOrganizationTest extends TestCase
{
    private ClientInterface&MockObject $client;

    private OrganizationRepository&MockObject $organizationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private UpdateOrganization $listener;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->organizationRepository = $this->createMock(OrganizationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->listener = new UpdateOrganization(
            new InsightlyClient(
                $this->client,
                'api-key',
                new Pipelines(['opportunities'=>['id' => 3, 'stages' => ['test'=> 4]]])
            ),
            $this->organizationRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function test_it_updates_an_organizer(): void
    {
        $organization = new Organization(
            Uuid::uuid4(),
            'Test Organization',
            'BE 0475 250 609',
            'facturatie@publiq.be',
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );
        $this->organizationRepository->expects($this->once())
            ->method('getById')
            ->with($organization->id)
            ->willReturn($organization);

        $insightlyId = 1234;
        $insightlyIntegrationMapping = new InsightlyMapping(
            $organization->id,
            $insightlyId,
            ResourceType::Organization,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('getById')
            ->with($organization->id)
            ->willReturn($insightlyIntegrationMapping);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(
                    function (Request $request) use ($organization, $insightlyId): bool {
                        $expected = [
                            'ORGANISATION_NAME' => $organization->name,
                            'ADDRESS_BILLING_STREET' => $organization->address->street,
                            'ADDRESS_BILLING_POSTCODE' => $organization->address->zip,
                            'ADDRESS_BILLING_CITY' => $organization->address->city,
                            'CUSTOMFIELDS' => [
                                [
                                    'FIELD_NAME' => 'Email_boekhouding__c',
                                    'CUSTOM_FIELD_ID' => 'Email_boekhouding__c',
                                    'FIELD_VALUE' => $organization->invoiceEmail,
                                ],
                                [
                                    'FIELD_NAME' => 'BTW_nummer__c',
                                    'CUSTOM_FIELD_ID' => 'BTW_nummer__c',
                                    'FIELD_VALUE' => $organization->vat,
                                ],
                            ],
                            'ORGANISATION_ID' => $insightlyId,
                        ];

                        return $request->getMethod() === 'PUT'
                            && $request->getUri()->getPath() === 'Organizations/'
                            && Json::decodeAssociatively((string) $request->getBody()) === $expected;
                    }
                )
            );

        $event = new OrganizationUpdated($organization->id);
        $this->listener->handle($event);
    }
}
