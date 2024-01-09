<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Controllers;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Models\IntegrationUrlModel;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class IntegrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_store_an_integration(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $subscriptionId = Uuid::uuid4();

        $response = $this->post(
            '/integrations',
            [
                'integrationType' => IntegrationType::SearchApi->value,
                'subscriptionId' => $subscriptionId->toString(),
                'integrationName' => 'Test Integration',
                'description' => 'Test Integration description',
                'firstNameFunctionalContact' => 'John',
                'lastNameFunctionalContact' => 'Doe',
                'emailFunctionalContact' => 'john.doe@test.com',
                'firstNameTechnicalContact' => 'John',
                'lastNameTechnicalContact' => 'Doe',
                'emailTechnicalContact' => 'john.doe@test.com',
                'agreement' => 'true',
                'privacy' => 'some privacy',
            ]
        );

        $response->assertRedirect('/nl/integraties');

        $this->assertDatabaseHas('integrations', [
            'type' => IntegrationType::SearchApi->value,
            'subscription_id' => $subscriptionId->toString(),
            'name' => 'Test Integration',
            'description' => 'Test Integration description',
            'status' => IntegrationStatus::Draft->value,
            'partner_status' => IntegrationPartnerStatus::THIRD_PARTY->value,
        ]);

        $this->assertDatabaseHas('contacts', [
            'type' => ContactType::Functional,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertDatabaseHas('contacts', [
            'type' => ContactType::Technical,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function test_it_can_destroy_an_integration(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);

        $response = $this->delete("/integrations/{$integration->id}");

        $response->assertRedirect('/nl/integraties/');

        $this->assertSoftDeleted('integrations', [
            'id' => $integration->id->toString()
        ]);
    }

    public function test_it_cant_destroy_an_integration_if_not_authorized(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();

        $response = $this->delete("/integrations/{$integration->id}");

        $response->assertForbidden();
    }

    public function test_it_can_activate_an_integration_with_a_coupon(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);
        $coupon = $this->givenThereIsACoupon();

        $response = $this->post(
            "/integrations/{$integration->id}/coupon",
            [
                'coupon' => $coupon->code,
            ]
        );

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'status' => IntegrationStatus::Active,
        ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'integration_id' => $integration->id->toString(),
        ]);
    }

    public function test_it_cant_activate_an_integration_with_a_coupon_if_not_authorized(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();
        $coupon = $this->givenThereIsACoupon();

        $response = $this->post(
            "/integrations/{$integration->id}/coupon",
            [
                'coupon' => $coupon->code,
            ]
        );

        $response->assertForbidden();
    }

    public function test_it_can_activate_an_integration_with_an_organization(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $organization = $this->givenThereIsAnOrganization();
        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);

        $response = $this->post(
            "/integrations/{$integration->id}/organization",
            [
                'organization' => [
                    'id' => $organization->id->toString(),
                    'name' => $organization->name,
                    'vat' => $organization->vat,
                    'invoiceEmail' => $organization->invoiceEmail,
                    'address' => [
                        'street' => $organization->address->street,
                        'zip' => $organization->address->zip,
                        'city' => $organization->address->city,
                        'country' => $organization->address->country,
                    ],
                ],
            ]
        );

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
        ]);

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'organization_id' => $organization->id,
            'status' => IntegrationStatus::Active,
        ]);
    }

    public function test_it_cant_activate_an_integration_with_an_organization_if_not_authorized(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $organization = $this->givenThereIsAnOrganization();
        $integration = $this->givenThereIsAnIntegration();

        $response = $this->post(
            "/integrations/{$integration->id}/organization",
            [
                'organization' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'vat' => $organization->vat,
                    'invoiceEmail' => $organization->invoiceEmail,
                    'address' => [
                        'street' => $organization->address->street,
                        'zip' => $organization->address->zip,
                        'city' => $organization->address->city,
                        'country' => $organization->address->country,
                    ],
                ],
            ]
        );

        $response->assertForbidden();
    }

    public function test_it_can_update_an_integration(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);

        $response = $this->patch("/integrations/{$integration->id}", [
            'integrationName' => 'updated name',
            'description' => 'updated description',
        ]);

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'name' => 'updated name',
            'description' => 'updated description'
        ]);
    }

    public function test_it_cant_update_an_integration_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();

        $response = $this->patch("/integrations/{$integration->id}", [
            'integrationName' => 'updated name',
            'description' => 'updated description',
        ]);

        $response->assertForbidden();
    }

    public function test_it_can_store_an_integration_url(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);

        $response = $this->post("/integrations/{$integration->id}/urls", [
            'environment' => Environment::Testing->value,
            'type' => IntegrationUrlType::Callback->value,
            'url' => 'https://localhost:3000',
        ]);

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseHas('integrations_urls', [
            'environment' => Environment::Testing->value,
            'type' => IntegrationUrlType::Callback->value,
            'url' => 'https://localhost:3000',
        ]);
    }

    public function test_it_cant_store_an_integration_url_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();

        $response = $this->post("/integrations/{$integration->id}/urls", [
            'environment' => Environment::Testing->value,
            'type' => IntegrationUrlType::Callback->value,
            'url' => 'https://localhost:3000',
        ]);

        $response->assertForbidden();
    }

    public function test_it_can_destroy_an_integration_url(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);
        $integrationUrl = $this->givenThereIsAnIntegrationUrlForIntegration($integration);

        $response = $this->delete("/integrations/{$integration->id}/urls/{$integrationUrl->id}");

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseMissing('integrations_urls', [
            'id' => $integrationUrl->id
        ]);
    }

    public function test_it_cant_destroy_an_integration_url_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();
        $integrationUrl = $this->givenThereIsAnIntegrationUrlForIntegration($integration);

        $response = $this->delete("/integrations/{$integration->id}/urls/{$integrationUrl->id}");

        $response->assertForbidden();
    }

    private function givenThereIsAnIntegration(): Integration
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

        IntegrationModel::query()->insert([
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $integration->subscriptionId,
            'status' => $integration->status,
            'partner_status' => IntegrationPartnerStatus::THIRD_PARTY,
        ]);

        return $integration;
    }

    private function givenThereIsAnIntegrationUrlForIntegration(Integration $integration): IntegrationUrl
    {
        $integrationUrl = new IntegrationUrl(
            Uuid::uuid4(),
            $integration->id,
            Environment::Testing,
            IntegrationUrlType::Login,
            'https://localhost:3000'
        );

        IntegrationUrlModel::query()->insert([
            'id' => $integrationUrl->id,
            'integration_id' => $integrationUrl->integrationId->toString(),
            'environment' => $integrationUrl->environment->value,
            'type' => $integrationUrl->type->value,
            'url' => 'https://localhost:3000'
        ]);

        return $integrationUrl;
    }

    private function givenTheActingUserIsAContactOnIntegration(Integration $integration): Contact
    {
        $user = Auth::user();

        if (!$user) {
            throw new UnauthorizedException();
        }

        $contact = new Contact(
            Uuid::uuid4(),
            $integration->id,
            $user->email,
            ContactType::Contributor,
            $user->first_name,
            $user->last_name,
        );

        ContactModel::query()->insert([
            'id' => $contact->id,
            'integration_id' => $contact->integrationId,
            'email' => $contact->email,
            'type' => $contact->type,
            'first_name' => $contact->firstName,
            'last_name' => $contact->lastName,
        ]);

        return $contact;
    }

    private function givenThereIsACoupon(): Coupon
    {
        $coupon = new Coupon(
            Uuid::uuid4(),
            false,
            null,
            '12345678901'
        );

        CouponModel::query()->insert([
            'id' => $coupon->id,
            'is_distributed' => $coupon->isDistributed,
            'integration_id' => $coupon->integrationId,
            'code' => $coupon->code,
        ]);

        return $coupon;
    }

    private function givenThereIsAnOrganization(): Organization
    {
        $organization = new Organization(
            Uuid::uuid4(),
            'Test Organization',
            'facturatie@publiq.be',
            'BE 0475 250 609',
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        OrganizationModel::query()->insert([
            'id' => $organization->id,
            'name' => $organization->name,
            'vat' => $organization->vat,
            'invoice_email' => $organization->invoiceEmail,
            'street' => $organization->address->street,
            'zip' => $organization->address->zip,
            'city' => $organization->address->city,
            'country' => $organization->address->country,
        ]);

        return $organization;
    }
}
