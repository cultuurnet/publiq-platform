<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Controllers;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class IntegrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_activate_an_integration_with_a_coupon(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $integration = $this->givenThereIsAnIntegration();
        $coupon = $this->givenThereIsACoupon();

        $response = $this->post(
            '/integrations/' . $integration->id . '/coupon',
            [
                'coupon' => $coupon->code,
            ]
        );
        dump($response->dump());
        $response->assertRedirect('/nl/integraties/' . $integration->id->toString());

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'status' => IntegrationStatus::Active,
        ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'integration_id' => $integration->id->toString(),
        ]);
    }

    public function test_it_can_active_an_integration_with_an_organization(): void
    {
        $this->actingAs(UserModel::createSystemUser(), 'web');

        $organization = $this->givenThereIsAnOrganization();
        $integration = $this->givenThereIsAnIntegration();

        $response = $this->post(
            '/integrations/' . $integration->id . '/organization',
            [
                'organisation' => [
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

        $response->assertRedirect('/nl/integraties/' . $integration->id->toString());

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
        ]);

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'organization_id' => $organization->id,
            'status' => IntegrationStatus::Active,
        ]);
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
