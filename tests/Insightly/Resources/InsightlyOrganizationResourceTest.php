<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use App\Insightly\Resources\InsightlyOrganizationResource;
use App\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Iterator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\AssertRequest;
use Tests\MockCrmClient;

final class InsightlyOrganizationResourceTest extends TestCase
{
    use MockCrmClient;
    use AssertRequest;

    private InsightlyOrganizationResource $resource;

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->resource = new InsightlyOrganizationResource($this->insightlyClient);
    }

    /**
     * @dataProvider provideCreateCases
     */
    public function test_it_creates_an_organization(Organization $organization, string $expectedRequest): void
    {
        $insightlyId = 42;
        $expectedRequest = new Request('POST', 'Organizations/', [], $expectedRequest);
        $expectedResponse = new Response(200, [], Json::encode(['ORGANISATION_ID' => $insightlyId]));

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest) => self::assertRequestIsTheSame($expectedRequest, $actualRequest)))
            ->willReturn($expectedResponse);

        $returnedId = $this->resource->create($organization);
        $this->assertEquals($insightlyId, $returnedId);
    }

    public function provideCreateCases(): Iterator
    {
        yield 'Organization without vat' => [
            'organization' => new Organization(
                Uuid::uuid4(),
                'madewithlove',
                null,
                new Address('Sluisstraat 79', '3000', 'Leuven', 'Belgium')
            ),
            'expectedRequest' => Json::encode([
                'ORGANISATION_NAME' => 'madewithlove',
                'ADDRESS_BILLING_STREET' => 'Sluisstraat 79',
                'ADDRESS_BILLING_POSTCODE' => '3000',
                'ADDRESS_BILLING_CITY' => 'Leuven',
            ]),
        ];

        yield 'Organization with vat' => [
            'organization' => new Organization(
                Uuid::uuid4(),
                'madewithlove',
                'BE1234567890',
                new Address('Sluisstraat 79', '3000', 'Leuven', 'Belgium')
            ),
            'expectedRequest' => Json::encode([
                'ORGANISATION_NAME' => 'madewithlove',
                'ADDRESS_BILLING_STREET' => 'Sluisstraat 79',
                'ADDRESS_BILLING_POSTCODE' => '3000',
                'ADDRESS_BILLING_CITY' => 'Leuven',
                'CUSTOMFIELDS' => [
                    [
                        'FIELD_NAME' => 'BTW_nummer__c',
                        'CUSTOM_FIELD_ID' => 'BTW_nummer__c',
                        'FIELD_VALUE' => 'BE1234567890',
                    ],
                ],
            ]),
        ];
    }
}
