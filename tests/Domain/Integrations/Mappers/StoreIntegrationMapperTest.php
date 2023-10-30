<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Auth\CurrentUser;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\FormRequests\StoreIntegrationRequest;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Mappers\StoreIntegrationMapper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\TestCase;
use Tests\UuidTestFactory;

final class StoreIntegrationMapperTest extends TestCase
{
    private const SUBSCRIPTION_ID = '4f1d3b79-dd1d-47d9-aa5a-2b55d32ea65f';
    private const USER_ID = 'c541a07b-068a-4f66-944f-90f8e64237da';
    private const INTEGRATION_ID = 'a8ab2245-17b4-44e3-9920-fab075effbdc';
    private const FUNCTIONAL_CONTACT_ID = '8549201e-961b-4022-8c37-497f3b599dbe';
    private const TECHNICAL_CONTACT_ID = 'bb43b31f-a297-4a41-bd6b-ed2188f4ea75';
    private const CONTRIBUTOR_CONTACT_ID = '43c9cb94-ec6f-4211-a0fb-d589223e0fd6';

    private array $inputs;
    private CurrentUser $currentUser;

    protected function setUp(): void
    {
        parent::setUp();

        Uuid::setFactory(new UuidTestFactory([
            'uuid4' => [
                self::INTEGRATION_ID,
                self::FUNCTIONAL_CONTACT_ID,
                self::TECHNICAL_CONTACT_ID,
                self::CONTRIBUTOR_CONTACT_ID,
            ],
        ]));

        $userModel = UserModel::fromSession([
            'user_id' => self::USER_ID,
            'email' => 'john.doe@test.com',
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Auth::shouldReceive('user')
            ->andReturn($userModel);

        $this->currentUser = new CurrentUser(App::get(Auth::class));

        $this->inputs = [
            'integrationType' => IntegrationType::SearchApi->value,
            'subscriptionId' => self::SUBSCRIPTION_ID,
            'integrationName' => 'My searches',
            'description' => 'To view my searches',
            'organisationFunctionalContact' => 'Tesla',
            'firstNameFunctionalContact' => 'John',
            'lastNameFunctionalContact' => 'Doe',
            'emailFunctionalContact' => 'john.doe@test.com',
            'organisationTechnicalContact' => 'Tesla',
            'firstNameTechnicalContact' => 'Jane',
            'lastNameTechnicalContact' => 'Doe',
            'emailTechnicalContact' => 'jane.doe@test.com',
            'agreement' => 'true',
            'privacy' => 'true',
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Uuid::setFactory(new UuidFactory());
    }

    private function getExpectedIntegration(): Integration
    {
        $integrationId = Uuid::fromString(self::INTEGRATION_ID);

        $functionalContact = new Contact(
            Uuid::fromString(self::FUNCTIONAL_CONTACT_ID),
            $integrationId,
            $this->inputs['emailFunctionalContact'],
            ContactType::Functional,
            $this->inputs['firstNameFunctionalContact'],
            $this->inputs['lastNameFunctionalContact']
        );

        $technicalContact = new Contact(
            Uuid::fromString(self::TECHNICAL_CONTACT_ID),
            $integrationId,
            $this->inputs['emailTechnicalContact'],
            ContactType::Technical,
            $this->inputs['firstNameTechnicalContact'],
            $this->inputs['lastNameTechnicalContact']
        );

        $contributorContact = new Contact(
            Uuid::fromString(self::CONTRIBUTOR_CONTACT_ID),
            $integrationId,
            $this->currentUser->email(),
            ContactType::Contributor,
            $this->currentUser->firstName(),
            $this->currentUser->lastName()
        );

        return (new Integration(
            $integrationId,
            IntegrationType::from($this->inputs['integrationType']),
            $this->inputs['integrationName'],
            $this->inputs['description'],
            Uuid::fromString($this->inputs['subscriptionId']),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY
        ))->withContacts($functionalContact, $technicalContact, $contributorContact);
    }

    public function test_it_creates_an_integration_from_request(): void
    {
        $request = new StoreIntegrationRequest();
        $request->merge($this->inputs);

        $actual = StoreIntegrationMapper::map($request, $this->currentUser);

        $expected = $this->getExpectedIntegration();

        $this->assertEquals($expected, $actual);
    }
}
