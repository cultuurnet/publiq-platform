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
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\Role;
use App\Insightly\Pipelines;
use App\Insightly\Resources\ResourceType;
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

    public function test_it_can_update_a_project_with_a_coupon(): void
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

        $this->insightlyClient->projects()->updateWithCoupon($insightlyId, 'test123');

        $this->insightlyClient->projects()->delete($insightlyId);
    }

    public function test_it_can_update_the_stage_of_a_project(): void
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

        $this->insightlyClient->projects()->updateStage($insightlyId, ProjectStage::LIVE);

        $this->insightlyClient->projects()->delete($insightlyId);
    }

    public function test_it_can_link_an_opportunity_to_a_project(): void
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

        $insightlyOpportunityId = $this->insightlyClient->opportunities()->create($integration);
        $this->assertNotNull($insightlyOpportunityId);

        $insightlyProjectId = $this->insightlyClient->projects()->create($integration);
        $this->assertNotNull($insightlyProjectId);

        $this->insightlyClient->projects()->linkOpportunity($insightlyProjectId, $insightlyOpportunityId);

        $this->insightlyClient->opportunities()->delete($insightlyOpportunityId);
        $this->insightlyClient->projects()->delete($insightlyProjectId);
    }

    public function test_it_can_link_a_contact_to_an_opportunity(): void
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

        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe'
        );

        $insightlyOpportunityId = $this->insightlyClient->opportunities()->create($integration);
        $insightlyContactId = $this->insightlyClient->contacts()->create($contact);

        $this->insightlyClient->opportunities()->linkContact(
            $insightlyOpportunityId,
            $insightlyContactId,
            ContactType::Technical
        );
        sleep(1);

        $result = $this->insightlyClient->opportunities()->get($insightlyOpportunityId);

        $this->assertEquals(Role::Technical->value, $result['LINKS'][0]['ROLE']);
        $this->assertEquals(ResourceType::Contact->name, $result['LINKS'][0]['LINK_OBJECT_NAME']);
        $this->assertEquals($insightlyContactId, $result['LINKS'][0]['LINK_OBJECT_ID']);

        $this->assertEquals(ResourceType::Opportunity->name, $result['LINKS'][0]['OBJECT_NAME']);
        $this->assertEquals($insightlyOpportunityId, $result['LINKS'][0]['OBJECT_ID']);

        $this->insightlyClient->opportunities()->delete($insightlyOpportunityId);
        $this->insightlyClient->contacts()->delete($insightlyContactId);
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
