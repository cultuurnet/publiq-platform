<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Insightly\Objects\InsightlyContact;
use App\Insightly\Objects\InsightlyContacts;
use App\Insightly\Resources\InsightlyContactResource;
use App\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\AssertRequest;
use Tests\MockInsightlyClient;

final class InsightlyContactResourceTest extends TestCase
{
    use MockInsightlyClient;
    use AssertRequest;

    private InsightlyContactResource $resource;

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->resource = new InsightlyContactResource($this->insightlyClient);
    }

    public function test_it_creates_a_contact(): void
    {
        $insightlyId = 42;
        $email = 'jane.doe@anonymous.com';
        $firstName = 'Jane';
        $lastName = 'Doe';

        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            $email,
            ContactType::Functional,
            $firstName,
            $lastName
        );

        $expectedRequest = new Request(
            'POST',
            'Contacts/',
            [],
            Json::encode([
                'FIRST_NAME' => $firstName,
                'LAST_NAME' => $lastName,
                'EMAIL_ADDRESS' => $email,
                'CUSTOMFIELDS' => [
                    [
                        'FIELD_NAME' => 'Relatie_contact__c',
                        'CUSTOM_FIELD_ID' => 'Relatie_contact__c',
                        'FIELD_VALUE' => 'Functioneel contact publiq platform',
                    ],
                ],
            ]),
        );

        $expectedResponse = new Response(200, [], Json::encode(['CONTACT_ID' => $insightlyId]));
        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)))
            ->willReturn($expectedResponse);

        $returnedId = $this->resource->create($contact);
        $this->assertEquals($insightlyId, $returnedId);
    }

    public function test_it_updates_a_contact(): void
    {
        $insightlyId = 42;
        $email = 'jane.doe@anonymous.com';
        $firstName = 'Jane';
        $lastName = 'Doe';

        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            $email,
            ContactType::Functional,
            $firstName,
            $lastName
        );

        $expectedRequest = new Request(
            'PUT',
            'Contacts/',
            [],
            Json::encode([
                'FIRST_NAME' => $firstName,
                'LAST_NAME' => $lastName,
                'EMAIL_ADDRESS' => $email,
                'CUSTOMFIELDS' => [
                    [
                        'FIELD_NAME' => 'Relatie_contact__c',
                        'CUSTOM_FIELD_ID' => 'Relatie_contact__c',
                        'FIELD_VALUE' => 'Functioneel contact publiq platform',
                    ],
                ],
                'CONTACT_ID' => $insightlyId,
            ]),
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->resource->update($contact, $insightlyId);
    }

    public function test_it_gets_a_contact(): void
    {
        $insightlyId = 42;
        $contact = [
            'CONTACT_ID' => $insightlyId,
            'FIRST_NAME' => 'Jane',
            'LAST_NAME' => 'Doe',
            'EMAIL_ADDRESS' => 'jane.doe@anonymous.com',
        ];

        $expectedRequest = new Request('GET', 'Contacts/' . $insightlyId);
        $expectedResponse = new Response(200, [], Json::encode($contact));

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)))
            ->willReturn($expectedResponse);

        $actualContact = $this->resource->get(42);
        $this->assertEquals($contact, $actualContact);
    }

    public function test_it_deletes_a_contact(): void
    {
        $expectedRequest = new Request('DELETE', 'Contacts/42');
        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->resource->delete(42);
    }

    public function test_it_can_search_contacts_on_email_address(): void
    {
        $expectedRequest = new Request(
            'GET',
            '/Contacts/Search?field_name=email_address&field_value=info@publiq.be&brief=true'
        );

        $foundContacts = [
            [
                'CONTACT_ID' => 42,
                'EMAIL_ADDRESS' => 'info@publiq.be',
                'LINKS' => [],
            ],
            [
                'CONTACT_ID' => 53,
                'EMAIL_ADDRESS' => 'info@publiq.be',
                'LINKS' => [
                    [
                        'OBJECT_ID' => 1,
                        'OBJECT_NAME' => 'Contact',
                    ],
                    [
                        'OBJECT_ID' => 2,
                        'OBJECT_NAME' => 'Opportunity',
                    ],
                ],
            ],
        ];

        $response = new Response(200, [], Json::encode($foundContacts));

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)))
            ->willReturn($response);

        $foundContactIds = $this->resource->findByEmail('info@publiq.be');

        $expectedFoundContacts = new InsightlyContacts([
            new InsightlyContact(42, 0),
            new InsightlyContact(53, 2),
        ]);
        $this->assertEquals($expectedFoundContacts, $foundContactIds);
    }
}
