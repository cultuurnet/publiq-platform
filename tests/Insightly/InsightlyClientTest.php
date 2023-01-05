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
use App\Insightly\HttpInsightlyClient;
use App\Insightly\Pipelines;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class InsightlyClientTest extends TestCase
{
    private HttpInsightlyClient $insightlyClient;

    protected function setUp(): void
    {
        $this->createApplication();

        $this->insightlyClient = new HttpInsightlyClient(
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

    public function test_it_can_create_and_update_a_contact(): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe'
        );

        $insightlyId = $this->insightlyClient->contacts()->create($contact);
        $this->assertNotNull($insightlyId);

        $updatedContact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jeanne',
            'Doe'
        );

        $this->insightlyClient->contacts()->update($updatedContact, $insightlyId);

        $this->insightlyClient->contacts()->delete($insightlyId);
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

        $insightlyId = $this->insightlyClient->opportunities()->create($integration);
        $this->assertNotNull($insightlyId);

        $this->insightlyClient->opportunities()->delete($insightlyId);
    }

    public function test_it_can_create_a_project(): void
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

        $insightlyId = $this->insightlyClient->projects()->create($integration);
        $this->assertNotNull($insightlyId);

        $this->insightlyClient->projects()->delete($insightlyId);
    }

    public function test_it_can_create_an_organization(): void
    {
        $organizationId = Uuid::uuid4();

        $organization = new Organization(
            $organizationId,
            'Test Organization',
            'facturatie@publiq.be',
            null,
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        $insightlyId = $this->insightlyClient->organizations()->create($organization);
        $this->assertNotNull($insightlyId);

        $this->insightlyClient->opportunities()->delete($insightlyId);
    }

    public function test_it_can_update_an_organization(): void
    {
        $organizationId = Uuid::uuid4();

        $organization = new Organization(
            $organizationId,
            'Test Organization',
            'facturatie@publiq.be',
            null,
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        $insightlyId = $this->insightlyClient->organizations()->create($organization);
        $this->assertNotNull($organizationId);

        $updatedOrganization = new Organization(
            $organizationId,
            'Updated Organization',
            'invoicing@publiq.be',
            null,
            new Address(
                'Sluisstraat 1',
                '3000',
                'Leuven',
                'België'
            )
        );
        $this->insightlyClient->organizations()->update($updatedOrganization, $insightlyId);

        $this->insightlyClient->opportunities()->delete($insightlyId);
    }
}
