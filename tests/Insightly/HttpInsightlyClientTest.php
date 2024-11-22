<?php

declare(strict_types=1);

namespace Tests\Insightly;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use App\Insightly\HttpInsightlyClient;
use App\Insightly\Objects\InsightlyContact;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\Role;
use App\Insightly\Pipelines;
use App\Insightly\Resources\ResourceType;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class HttpInsightlyClientTest extends TestCase
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
        $this->assertNotEmpty($insightlyId);

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

    public function test_it_can_find_a_contact_by_email(): void
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
        $this->assertNotEmpty($insightlyId);

        $insightlyContacts = $this->insightlyClient->contacts()->findByEmail('jane.doe@anonymous.com');
        $contactIds = array_map(
            static fn (InsightlyContact $contact) => $contact->insightlyId,
            $insightlyContacts->toArray()
        );
        $this->assertContains($insightlyId, $contactIds);

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
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $insightlyId = $this->insightlyClient->opportunities()->create($integration);
        $this->assertNotEmpty($insightlyId);

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
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $insightlyId = $this->insightlyClient->projects()->create($integration);
        $this->assertNotEmpty($insightlyId);

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
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $insightlyId = $this->insightlyClient->projects()->create($integration);
        $this->assertNotEmpty($insightlyId);

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
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $insightlyId = $this->insightlyClient->projects()->create($integration);
        $this->assertNotEmpty($insightlyId);

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
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $insightlyOpportunityId = $this->insightlyClient->opportunities()->create($integration);
        $this->assertNotEmpty($insightlyOpportunityId);

        $insightlyProjectId = $this->insightlyClient->projects()->create($integration);
        $this->assertNotEmpty($insightlyProjectId);

        $this->insightlyClient->projects()->linkOpportunity($insightlyProjectId, $insightlyOpportunityId);

        $projectAsArray = $this->insightlyClient->projects()->get($insightlyProjectId);

        $this->assertEquals($insightlyProjectId, $projectAsArray['LINKS'][0]['OBJECT_ID']);
        $this->assertEquals('Project', $projectAsArray['LINKS'][0]['OBJECT_NAME']);
        $this->assertEquals($insightlyOpportunityId, $projectAsArray['LINKS'][0]['LINK_OBJECT_ID']);
        $this->assertEquals('Opportunity', $projectAsArray['LINKS'][0]['LINK_OBJECT_NAME']);

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
            IntegrationPartnerStatus::THIRD_PARTY,
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
        $result = $this->insightlyClient->opportunities()->get($insightlyOpportunityId);
        $this->assertEquals($insightlyOpportunityId, $result['OPPORTUNITY_ID']);

        $insightlyContactId = $this->insightlyClient->contacts()->create($contact);
        $result = $this->insightlyClient->contacts()->get($insightlyContactId);
        $this->assertEquals($insightlyContactId, $result['CONTACT_ID']);

        $this->insightlyClient->opportunities()->linkContact(
            $insightlyOpportunityId,
            $insightlyContactId,
            ContactType::Technical
        );

        $opportunityAsArray = $this->insightlyClient->opportunities()->get($insightlyOpportunityId);

        $this->assertEquals(Role::Technical->value, $opportunityAsArray['LINKS'][0]['ROLE']);
        $this->assertEquals(ResourceType::Contact->name, $opportunityAsArray['LINKS'][0]['LINK_OBJECT_NAME']);
        $this->assertEquals($insightlyContactId, $opportunityAsArray['LINKS'][0]['LINK_OBJECT_ID']);
        $this->assertEquals(ResourceType::Opportunity->name, $opportunityAsArray['LINKS'][0]['OBJECT_NAME']);
        $this->assertEquals($insightlyOpportunityId, $opportunityAsArray['LINKS'][0]['OBJECT_ID']);

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
        $this->assertNotEmpty($insightlyId);

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
        $this->assertNotEmpty($insightlyId);

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
