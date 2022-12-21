<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Insightly\Resources\InsightlyContactResource;
use App\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\MockCrmClient;

final class InsightlyContactResourceTest extends TestCase
{
    use MockCrmClient;

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

    private static function assertRequestIsTheSame(Request $expected, Request $actual): bool
    {
        self::assertEquals($expected->getHeaders(), $actual->getHeaders());
        self::assertEquals($expected->getMethod(), $actual->getMethod());
        self::assertEquals($expected->getBody()->getContents(), $actual->getBody()->getContents());

        return true;
    }
}
