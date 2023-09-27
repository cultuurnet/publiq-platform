<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Auth\CurrentUser;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\FormRequests\StoreIntegrationRequest;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Mappers\StoreIntegrationMapper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class StoreIntegrationMapperTest extends TestCase
{
    private array $inputs;
    private CurrentUser $currentUser;

    protected function setUp(): void
    {
        parent::setUp();

        $userModel = UserModel::fromSession([
            'id' => Uuid::uuid4()->toString(),
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
            'subscriptionId' => Uuid::uuid4()->toString(),
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
        ];
    }

    private function getExpectedIntegration(): Integration
    {
        $integrationId = Uuid::uuid4();

        $functionalContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $this->inputs['emailFunctionalContact'],
            ContactType::Functional,
            $this->inputs['firstNameFunctionalContact'],
            $this->inputs['lastNameFunctionalContact']
        );

        $technicalContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            $this->inputs['emailTechnicalContact'],
            ContactType::Technical,
            $this->inputs['firstNameTechnicalContact'],
            $this->inputs['lastNameTechnicalContact']
        );

        $contributor = new Contact(
            Uuid::uuid4(),
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
            IntegrationStatus::Draft
        ))->withContacts($functionalContact, $technicalContact, $contributor);
    }

    private function assertIntegrationData(Integration $expected, Integration $actual): void
    {
        $this->assertEquals($expected->type, $actual->type);
        $this->assertEquals($expected->name, $actual->name);
        $this->assertEquals($expected->description, $actual->description);
        $this->assertEquals($expected->subscriptionId, $actual->subscriptionId);
        $this->assertEquals($expected->status, $actual->status);
    }

    private function assertContactData(Contact $expected, Contact $actual): void
    {
        $this->assertEquals($expected->type, $actual->type);
        $this->assertEquals($expected->email, $actual->email);
        $this->assertEquals($expected->firstName, $actual->firstName);
        $this->assertEquals($expected->lastName, $actual->lastName);
    }

    public function test_it_creates_an_integration_from_request(): void
    {
        $request = new StoreIntegrationRequest();
        $request->merge($this->inputs);

        $actualIntegration = StoreIntegrationMapper::map($request, $this->currentUser);
        $actualContacts = $actualIntegration->contacts();

        $expectedIntegration = $this->getExpectedIntegration();
        $expectedContacts = $expectedIntegration->contacts();

        $this->assertIntegrationData($expectedIntegration, $actualIntegration);

        $this->assertContactData($expectedContacts[0], $actualContacts[0]);
        $this->assertContactData($expectedContacts[1], $actualContacts[1]);
        $this->assertContactData($expectedContacts[2], $actualContacts[2]);
    }
}
