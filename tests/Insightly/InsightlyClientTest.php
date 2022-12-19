<?php

declare(strict_types=1);

namespace Tests\Insightly;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use App\Insightly\InsightlyClient;
use App\Insightly\Pipelines;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class InsightlyClientTest extends TestCase
{
    private InsightlyClient $insightlyClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->insightlyClient = new InsightlyClient(
            new Client(
                [
                    'base_uri' => config('insightly.host'),
                    'http_errors' => false,
                ]
            ),
            config('insightly.api_key'),
            new Pipelines(config('insightly.pipelines'))
        );
    }

    public function test_it_can_create_a_contact(): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe'
        );

        $contactId = $this->insightlyClient->contacts()->create($contact);
        $this->assertNotNull($contactId);

        $this->insightlyClient->contacts()->delete($contactId);
    }

    public function test_it_can_create_an_opportunity(): void
    {
        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            []
        );

        $contactId = $this->insightlyClient->opportunities()->create($integration);
        $this->assertNotNull($contactId);

        $this->insightlyClient->opportunities()->delete($contactId);
    }

    public function test_it_can_create_an_organization(): void
    {
        $organizationId = Uuid::uuid4();

        $organization = new Organization(
            $organizationId,
            'Test Organization',
            null,
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        $organizationId = $this->insightlyClient->organizations()->create($organization);
        $this->assertNotNull($organizationId);

        $this->insightlyClient->opportunities()->delete($organizationId);
    }
}
