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
use App\ProjectAanvraag\ProjectAanvraagUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class IntegrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_store_an_integration(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $subscriptionId = Uuid::uuid4();

        $response = $this->post(
            '/integrations',
            [
                'integrationType' => IntegrationType::SearchApi->value,
                'subscriptionId' => $subscriptionId->toString(),
                'integrationName' => 'Test Integration',
                'description' => 'Test Integration description',
                'firstNameFunctionalContact' => 'Jack',
                'lastNameFunctionalContact' => 'Bauer',
                'emailFunctionalContact' => 'jack.bauer@test.com',
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
            'first_name' => 'Jack',
            'last_name' => 'Bauer',
        ]);

        $this->assertDatabaseHas('contacts', [
            'type' => ContactType::Technical,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function test_it_can_destroy_an_integration(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);

        $response = $this->delete("/integrations/{$integration->id}");

        $response->assertRedirect('/nl/integraties/');

        $this->assertSoftDeleted('integrations', [
            'id' => $integration->id->toString(),
        ]);
    }

    public function test_it_can_not_destroy_an_integration_if_not_authorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();

        $response = $this->delete("/integrations/{$integration->id}");

        $response->assertForbidden();

        $this->assertNotSoftDeleted('integrations', [
            'id' => $integration->id->toString(),
        ]);
    }

    public function test_it_can_activate_an_integration_with_a_coupon(): void
    {
        $this->actingAs(UserModel::createSystemUser());

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

    public function test_it_can_not_activate_an_integration_with_a_coupon_if_not_authorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $coupon = $this->givenThereIsACoupon();

        $response = $this->post(
            "/integrations/{$integration->id}/coupon",
            [
                'coupon' => $coupon->code,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('integrations', [
            'id' => $integration->id->toString(),
            'status' => IntegrationStatus::Active,
        ]);

        $this->assertDatabaseMissing('coupons', [
            'id' => $coupon->id,
            'integration_id' => $integration->id->toString(),
        ]);
    }

    public function test_it_can_activate_an_integration_with_an_organization(): void
    {
        $this->actingAs(UserModel::createSystemUser());

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

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'organization_id' => $organization->id,
            'status' => IntegrationStatus::Active,
        ]);
    }

    public function test_it_can_not_activate_an_integration_with_an_organization_if_not_authorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

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

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'organization_id' => null,
            'status' => IntegrationStatus::Draft,
        ]);
    }

    public function test_it_can_update_an_integration(): void
    {
        $this->actingAs(UserModel::createSystemUser());

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
            'description' => 'updated description',
        ]);
    }

    public function test_it_can_not_update_an_integration_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();

        $response = $this->patch("/integrations/{$integration->id}", [
            'integrationName' => 'updated name',
            'description' => 'updated description',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('integrations', [
            'id' => $integration->id->toString(),
            'name' => 'updated name',
            'description' => 'updated description',
        ]);
    }

    public function test_it_can_store_an_integration_url(): void
    {
        $this->actingAs(UserModel::createSystemUser());

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

    public function test_it_can_not_store_an_integration_url_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();

        $response = $this->post("/integrations/{$integration->id}/urls", [
            'environment' => Environment::Testing->value,
            'type' => IntegrationUrlType::Callback->value,
            'url' => 'https://localhost:3000',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('integrations_urls', [
            'environment' => Environment::Testing->value,
            'type' => IntegrationUrlType::Callback->value,
            'url' => 'https://localhost:3000',
        ]);
    }

    public function test_it_can_destroy_an_integration_url(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);
        $integrationUrl = $this->givenThereIsALoginUrlForIntegration($integration);

        $response = $this->delete("/integrations/{$integration->id}/urls/{$integrationUrl->id}");

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseMissing('integrations_urls', [
            'id' => $integrationUrl->id,
        ]);
    }

    public function test_it_can_not_destroy_an_integration_url_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $integrationUrl = $this->givenThereIsALoginUrlForIntegration($integration);

        $response = $this->delete("/integrations/{$integration->id}/urls/{$integrationUrl->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('integrations_urls', [
            'id' => $integrationUrl->id,
        ]);
    }

    public function test_it_can_update_integration_urls(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);
        $urls = $this->givenThereAreMultipleUrlsForIntegration($integration);

        $response = $this->put("/integrations/{$integration->id}/urls", [
            'urls' => [
                [
                    'id' => $urls->loginUrl->id->toString(),
                    'url' => 'https://updated.login',
                ],
                [
                    'id' => $urls->callbackUrls[0]->id->toString(),
                    'environment' => $urls->callbackUrls[0]->environment->value,
                    'type' => $urls->callbackUrls[0]->type->value,
                    'url' => 'https://updated.callback',
                ],
                [
                    'environment' => Environment::Acceptance->value,
                    'type' => IntegrationUrlType::Callback->value,
                    'url' => 'https://new.callback',
                ],
            ],
        ]);

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseHas('integrations_urls', [
            'id' => $urls->loginUrl->id->toString(),
            'type' => IntegrationUrlType::Login->value,
            'url' => 'https://updated.login',
        ]);

        $this->assertDatabaseHas('integrations_urls', [
            'id' => $urls->callbackUrls[0]->id->toString(),
            'type' => IntegrationUrlType::Callback->value,
            'url' => 'https://updated.callback',
        ]);

        $this->assertDatabaseHas('integrations_urls', [
            'environment' => Environment::Acceptance->value,
            'type' => IntegrationUrlType::Callback->value,
            'url' => 'https://new.callback',
        ]);

        $this->assertDatabaseMissing('integrations_urls', [
            'id' => $urls->logoutUrls[0]->id->toString(),
        ]);
    }

    public function test_it_can_add_integration_urls_via_update(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);

        $response = $this->put("/integrations/{$integration->id}/urls", [
            'urls' => [
                [
                    'environment' => Environment::Testing->value,
                    'type' => IntegrationUrlType::Login->value,
                    'url' => 'https://new.login',
                ],
            ],
        ]);

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseCount('integrations_urls', 1);

        $this->assertDatabaseHas('integrations_urls', [
            'environment' => Environment::Testing->value,
            'type' => IntegrationUrlType::Login->value,
            'url' => 'https://new.login',
        ]);
    }

    public function test_it_can_delete_integration_urls_via_update(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);
        $this->givenThereAreMultipleUrlsForIntegration($integration);

        $response = $this->put("/integrations/{$integration->id}/urls", [
            'urls' => [],
        ]);

        $response->assertRedirect("/nl/integraties/{$integration->id}");

        $this->assertDatabaseCount('integrations_urls', 0);
    }

    public function test_it_can_not_update_integration_urls_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $urls = $this->givenThereAreMultipleUrlsForIntegration($integration);

        $response = $this->put("/integrations/{$integration->id}/urls", [
            'urls' => [
                [
                    'id' => $urls->loginUrl->id->toString(),
                    'url' => 'https://updated.login',
                ],
                'callbackUrl' => [
                    'id' => $urls->callbackUrls[0]->id->toString(),
                    'url' => 'https://updated.callback',
                ],
                'logoutUrl' => [
                    'id' => $urls->logoutUrls[0]->id->toString(),
                    'url' => 'https://updated.logout',
                ],
            ],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('integrations_urls', [
            'id' => $urls->loginUrl->id->toString(),
            'type' => IntegrationUrlType::Login->value,
            'url' => $urls->loginUrl->url,
        ]);

        $this->assertDatabaseHas('integrations_urls', [
            'id' => $urls->callbackUrls[0]->id->toString(),
            'type' => IntegrationUrlType::Callback->value,
            'url' => $urls->callbackUrls[0]->url,
        ]);

        $this->assertDatabaseHas('integrations_urls', [
            'id' => $urls->logoutUrls[0]->id->toString(),
            'type' => IntegrationUrlType::Logout->value,
            'url' => $urls->logoutUrls[0]->url,
        ]);
    }

    public function test_it_can_update_contacts(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);
        $functionalContact = $this->givenThereIsAFunctionalContactOnIntegration($integration);

        $response = $this->patch(
            "/integrations/{$integration->id}/contacts",
            [
                'functional' => [
                    'id' => $functionalContact->id->toString(),
                    'integrationId' => $integration->id->toString(),
                    'email' => 'other@test.com',
                    'type' => $functionalContact->type->value,
                    'firstName' => $functionalContact->firstName,
                    'lastName' => $functionalContact->lastName,
                ],
            ]
        );

        $response->assertRedirect('/');

        $this->assertDatabaseHas('contacts', [
            'id' => $functionalContact->id->toString(),
            'integration_id' => $integration->id->toString(),
            'email' => 'other@test.com',
            'type' => $functionalContact->type->value,
            'first_name' => $functionalContact->firstName,
            'last_name' => $functionalContact->lastName,
        ]);
    }

    public function test_it_can_not_update_contacts_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $functionalContact = $this->givenThereIsAFunctionalContactOnIntegration($integration);

        $response = $this->patch(
            "/integrations/{$integration->id}/contacts",
            [
                'functional' => [
                    'id' => $functionalContact->id->toString(),
                    'integrationId' => $integration->id->toString(),
                    'email' => 'other@test.com',
                    'type' => $functionalContact->type->value,
                    'firstName' => $functionalContact->firstName,
                    'lastName' => $functionalContact->lastName,
                ],
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('contacts', [
            'id' => $functionalContact->id->toString(),
            'integration_id' => $integration->id->toString(),
            'email' => $functionalContact->email,
            'type' => $functionalContact->type->value,
            'first_name' => $functionalContact->firstName,
            'last_name' => $functionalContact->lastName,
        ]);
    }

    public function test_it_can_destroy_a_contact(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);
        $functionalContact = $this->givenThereIsAFunctionalContactOnIntegration($integration);

        $this->delete("/integrations/{$integration->id}/contacts/{$functionalContact->id}");

        $this->assertSoftDeleted('contacts', [
            'id' => $functionalContact->id->toString(),
        ]);
    }

    public function test_it_can_not_destroy_a_contact_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $functionalContact = $this->givenThereIsAFunctionalContactOnIntegration($integration);

        $response = $this->delete("/integrations/{$integration->id}/contacts/{$functionalContact->id}");

        $response->assertForbidden();

        $this->assertNotSoftDeleted('contacts', [
            'id' => $functionalContact->id->toString(),
        ]);
    }

    public function test_it_can_update_billing_info_of_organization(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $this->givenTheActingUserIsAContactOnIntegration($integration);
        $organization = $this->givenThereIsAnOrganization();
        $this->givenTheIntegrationIsActivatedWithOrganisation($integration, $organization);

        $this->patch("/integrations/{$integration->id}/organization", [
            'organization' => [
                'id' => $organization->id->toString(),
                'name' => 'updated',
                'vat' => 'updated',
                'invoiceEmail' => 'updated@test.com',
                'address' => [
                    'street' => 'updated',
                    'zip' => '0000',
                    'city' => 'updated',
                    'country' => 'updated',
                ],
            ],
        ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id->toString(),
            'name' => 'updated',
            'vat' => 'updated',
            'invoice_email' => 'updated@test.com',
            'street' => 'updated',
            'zip' => '0000',
            'city' => 'updated',
            'country' => 'updated',
        ]);
    }

    public function test_it_can_not_update_billing_info_of_organization_if_unauthorized(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $integration = $this->givenThereIsAnIntegration();
        $organization = $this->givenThereIsAnOrganization();
        $this->givenTheIntegrationIsActivatedWithOrganisation($integration, $organization);

        $response = $this->patch("/integrations/{$integration->id}/organization", [
            'organization' => [
                'id' => $organization->id->toString(),
                'name' => 'updated',
                'vat' => 'updated',
                'invoiceEmail' => 'updated@test.com',
                'address' => [
                    'street' => 'updated',
                    'zip' => '0000',
                    'city' => 'updated',
                    'country' => 'updated',
                ],
            ],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id->toString(),
            'name' => $organization->name,
            'vat' => $organization->vat,
            'invoice_email' => $organization->invoiceEmail,
            'street' => $organization->address->street,
            'zip' => $organization->address->zip,
            'city' => $organization->address->city,
            'country' => $organization->address->country,
        ]);
    }

    public function test_it_can_show_widget(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $widgetIntegration = $this->givenThereIsAnIntegration(IntegrationType::Widgets);
        $this->givenTheActingUserIsAContactOnIntegration($widgetIntegration);

        $response = $this->get("/integrations/{$widgetIntegration->id}/widget");

        $response->assertRedirect(ProjectAanvraagUrl::getForIntegration($widgetIntegration));
    }

    public function test_it_can_not_show_widget_if_not_authenticated(): void
    {
        $this->actingAs(UserModel::createSystemUser());

        $widgetIntegration = $this->givenThereIsAnIntegration(IntegrationType::Widgets);

        $response = $this->get("/integrations/{$widgetIntegration->id}/widget");

        $response->assertForbidden();
    }

    private function givenThereIsAnIntegration(IntegrationType $integrationType = null): Integration
    {
        $integration = new Integration(
            Uuid::uuid4(),
            $integrationType ?? IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        IntegrationModel::query()->insert([
            'id' => $integration->id->toString(),
            'type' => $integration->type->value,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $integration->subscriptionId->toString(),
            'status' => $integration->status->value,
            'partner_status' => $integration->partnerStatus->value,
        ]);

        return $integration;
    }

    private function givenThereIsALoginUrlForIntegration(Integration $integration): IntegrationUrl
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
            'url' => 'https://localhost:3000',
        ]);

        return $integrationUrl;
    }

    private function givenThereAreMultipleUrlsForIntegration(Integration $integration): IntegrationUrlsContainer
    {
        $callbackUrl = new IntegrationUrl(
            Uuid::uuid4(),
            $integration->id,
            Environment::Production,
            IntegrationUrlType::Callback,
            'https://localhost:3000/callback'
        );

        $loginUrl = new IntegrationUrl(
            Uuid::uuid4(),
            $integration->id,
            Environment::Testing,
            IntegrationUrlType::Login,
            'https://localhost:3000/login'
        );

        $logoutUrl = new IntegrationUrl(
            Uuid::uuid4(),
            $integration->id,
            Environment::Acceptance,
            IntegrationUrlType::Logout,
            'https://localhost:3000/logout'
        );

        DB::transaction(function () use ($callbackUrl, $loginUrl, $logoutUrl) {
            IntegrationUrlModel::query()->insert([
                'id' => $callbackUrl->id,
                'integration_id' => $callbackUrl->integrationId->toString(),
                'environment' => $callbackUrl->environment->value,
                'type' => $callbackUrl->type->value,
                'url' => $callbackUrl->url,
            ]);

            IntegrationUrlModel::query()->insert([
                'id' => $loginUrl->id,
                'integration_id' => $loginUrl->integrationId->toString(),
                'environment' => $loginUrl->environment->value,
                'type' => $loginUrl->type->value,
                'url' => $loginUrl->url,
            ]);

            IntegrationUrlModel::query()->insert([
                'id' => $logoutUrl->id,
                'integration_id' => $logoutUrl->integrationId->toString(),
                'environment' => $logoutUrl->environment->value,
                'type' => $logoutUrl->type->value,
                'url' => $logoutUrl->url,
            ]);

        });

        return new IntegrationUrlsContainer(
            $loginUrl,
            [$callbackUrl],
            [$logoutUrl],
        );
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

    private function givenThereIsAFunctionalContactOnIntegration(Integration $integration): Contact
    {
        $contact = new Contact(
            Uuid::uuid4(),
            $integration->id,
            'jane.doe@test.com',
            ContactType::Functional,
            'Jane',
            'Doe',
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

    private function givenTheIntegrationIsActivatedWithOrganisation(Integration $integration, Organization $organization): void
    {
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
    }
}
