<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Exceptions\InconsistentIntegrationType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\EloquentIntegrationRepository;
use App\Domain\Integrations\Repositories\EloquentUdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Integrations\Website;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\EloquentSubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Domain\UdbUuid;
use App\Mails\Template\TemplateName;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\TestCase;

final class EloquentIntegrationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentIntegrationRepository $integrationRepository;
    private EloquentSubscriptionRepository $subscriptionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = new EloquentIntegrationRepository(
            new EloquentUdbOrganizerRepository(),
            new EloquentSubscriptionRepository(),
        );
        $this->subscriptionRepository = new EloquentSubscriptionRepository();
    }

    public function test_it_can_save_an_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $subscriptionId = Uuid::uuid4();

        $subscription = $this->givenThereIsASubscription($subscriptionId);

        $technicalContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe',
        );

        $organizationContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            'john.doe@anonymous.com',
            ContactType::Functional,
            'John',
            'Doe'
        );

        $contributor = new Contact(
            Uuid::uuid4(),
            $integrationId,
            'jimmy.doe@anonymous.com',
            ContactType::Contributor,
            'Jimmy',
            'Doe'
        );

        $contacts = [$technicalContact, $organizationContact, $contributor];

        $integration = (new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            $subscriptionId,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))
            ->withContacts(...$contacts)
            ->withWebsite(new Website('https://www.publiq.be'));

        $this->integrationRepository->save($integration);

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $subscriptionId,
            'status' => $integration->status,
            'website' => 'https://www.publiq.be',
        ]);

        foreach ($integration->contacts() as $contact) {
            $this->assertDatabaseHas('contacts', [
                'id' => $contact->id->toString(),
                'integration_id' => $contact->integrationId->toString(),
                'type' => $contact->type,
                'first_name' => $contact->firstName,
                'last_name' => $contact->lastName,
                'email' => $contact->email,
            ]);
        }
    }

    public function test_it_can_update_an_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();
        $subscriptionId = Uuid::uuid4();

        $subscription = $this->givenThereIsASubscription($subscriptionId);

        $initialIntegration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Initial Integration',
            'Initial Integration description',
            $subscriptionId,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($initialIntegration);

        // @see https://jira.publiq.be/browse/PPF-246
        $secondIntegration = new Integration(
            $secondIntegrationId,
            IntegrationType::SearchApi,
            'Second Integration',
            'Second Integration description',
            $subscriptionId,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($secondIntegration);

        $updatedIntegration = (new Integration(
            $initialIntegration->id,
            $initialIntegration->type,
            'Updated Integration',
            'Updated Integration description',
            $initialIntegration->subscriptionId,
            IntegrationStatus::Active,
            $initialIntegration->partnerStatus,
        ))->withKeyVisibility(KeyVisibility::all)
            ->withSubscription($subscription);

        $this->integrationRepository->update($updatedIntegration);

        $retrievedIntegration = $this->integrationRepository->getById($integrationId);

        $this->assertEquals($updatedIntegration, $retrievedIntegration);
    }

    public function test_it_can_get_an_integration_by_id(): void
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

        $integrationFromRepository = $this->integrationRepository->getById($integration->id);

        $this->assertEquals($integration, $integrationFromRepository);
    }

    public function test_it_can_get_a_deleted_integration_by_id(): void
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

        $this->integrationRepository->deleteById($integration->id);

        $this->expectException(ModelNotFoundException::class);

        $this->integrationRepository->getById($integration->id);

        $deletedIntegration = new Integration(
            $integration->id,
            $integration->type,
            $integration->name,
            $integration->description,
            $integration->subscriptionId,
            IntegrationStatus::Deleted,
            $integration->partnerStatus
        );

        $integrationFromRepository = $this->integrationRepository->getByIdWithTrashed($integration->id);

        $this->assertEquals($deletedIntegration, $integrationFromRepository);
    }

    public function test_it_can_get_integrations_by_contact_email(): void
    {
        $searchIntegrationId = Uuid::uuid4();

        $technicalContact = new Contact(
            Uuid::uuid4(),
            $searchIntegrationId,
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe',
        );

        $organizationContact = new Contact(
            Uuid::uuid4(),
            $searchIntegrationId,
            'john.doe@anonymous.com',
            ContactType::Functional,
            'John',
            'Doe'
        );

        $widgetsCustomSubscription = $this->givenThereIsASubscription(
            category: SubscriptionCategory::Basic,
            integrationType: IntegrationType::SearchApi
        );

        $searchIntegration = (new Integration(
            $searchIntegrationId,
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            $widgetsCustomSubscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withContacts($technicalContact, $organizationContact)->withSubscription($widgetsCustomSubscription);

        $this->integrationRepository->save($searchIntegration);

        $widgetsIntegrationId = Uuid::uuid4();

        $contributor = new Contact(
            Uuid::uuid4(),
            $widgetsIntegrationId,
            'jane.doe@anonymous.com',
            ContactType::Contributor,
            'Jane',
            'Doe',
        );

        $otherTechnicalContact = new Contact(
            Uuid::uuid4(),
            $widgetsIntegrationId,
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe',
        );

        $widgetsCustomSubscription = $this->givenThereIsASubscription(
            category: SubscriptionCategory::Custom,
            integrationType: IntegrationType::Widgets,
        );

        $widgetsIntegration = (new Integration(
            $widgetsIntegrationId,
            IntegrationType::Widgets,
            'Widgets Integration',
            'Widgets Integration description',
            $widgetsCustomSubscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withContacts($contributor, $otherTechnicalContact)->withSubscription($widgetsCustomSubscription);

        $this->integrationRepository->save($widgetsIntegration);

        $foundIntegrations = $this->integrationRepository->getByContactEmail('jane.doe@anonymous.com')->collection;

        $this->assertCount(2, $foundIntegrations);
        $this->assertTrue($foundIntegrations->contains($searchIntegration));
        $this->assertTrue($foundIntegrations->contains($widgetsIntegration));
    }

    public function test_it_can_delete_an_integration(): void
    {
        $subscription = $this->givenThereIsASubscription(Uuid::uuid4());

        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($integration);

        $this->integrationRepository->deleteById($integration->id);

        $this->assertSoftDeleted('integrations', [
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $integration->subscriptionId,
            'status' => IntegrationStatus::Deleted,
        ]);
    }

    public function test_it_can_request_activation(): void
    {
        $integrationId = Uuid::uuid4();

        $subscription = $this->givenThereIsASubscription();

        $searchIntegration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($searchIntegration);

        $organizers = new UdbOrganizers(
            [
                new UdbOrganizer(
                    Uuid::uuid4(),
                    Uuid::uuid4(),
                    new UdbUuid(Uuid::uuid4()->toString()),
                    UdbOrganizerStatus::Pending
                ),
            ],
        );

        $organizationId = Uuid::uuid4();
        $this->integrationRepository->requestActivation($integrationId, $organizationId, null, $organizers);

        $this->assertDatabaseHas('integrations', [
            'id' => $searchIntegration->id->toString(),
            'type' => $searchIntegration->type,
            'name' => $searchIntegration->name,
            'description' => $searchIntegration->description,
            'subscription_id' => $searchIntegration->subscriptionId,
            'organization_id' => $organizationId,
            'status' => IntegrationStatus::PendingApprovalIntegration,
        ]);

        foreach ($organizers as $organizer) {
            $this->assertDatabaseHas('udb_organizers', [
                'id' => $organizer->id->toString(),
                'integration_id' => $organizer->integrationId->toString(),
                'organizer_id' => $organizer->organizerId,
            ]);
        }

        Event::assertDispatched(IntegrationActivationRequested::class);
    }

    public function test_it_can_request_activation_with_coupon(): void
    {
        $couponId = uuid::uuid4();
        $couponCode = '123';
        CouponModel::query()->insert([
            'id' => $couponId->toString(),
            'is_distributed' => false,
            'integration_id' => null,
            'code' => $couponCode,
        ]);

        $subscription = $this->givenThereIsASubscription();

        $integrationId = Uuid::uuid4();
        $searchIntegration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($searchIntegration);

        $organizationId = Uuid::uuid4();
        $this->integrationRepository->requestActivation($integrationId, $organizationId, $couponCode);

        $this->assertDatabaseHas('integrations', [
            'id' => $searchIntegration->id->toString(),
            'type' => $searchIntegration->type,
            'name' => $searchIntegration->name,
            'description' => $searchIntegration->description,
            'subscription_id' => $searchIntegration->subscriptionId,
            'organization_id' => $organizationId,
            'status' => IntegrationStatus::PendingApprovalIntegration,
        ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $couponId->toString(),
            'is_distributed' => true,
            'integration_id' => $searchIntegration->id->toString(),
            'code' => $couponCode,
        ]);

        Event::assertDispatched(IntegrationActivationRequested::class);
    }

    public function test_it_can_activate(): void
    {
        $integrationId = Uuid::uuid4();

        $subscription = $this->givenThereIsASubscription();

        $searchIntegration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($searchIntegration);

        $this->integrationRepository->activate($integrationId);

        $this->assertDatabaseHas('integrations', [
            'id' => $searchIntegration->id->toString(),
            'type' => $searchIntegration->type,
            'name' => $searchIntegration->name,
            'description' => $searchIntegration->description,
            'subscription_id' => $searchIntegration->subscriptionId,
            'status' => IntegrationStatus::Active,
        ]);

        Event::assertDispatched(IntegrationActivated::class);
    }

    public function test_it_can_activate_with_organization(): void
    {
        $integrationId = Uuid::uuid4();

        $subscription = $this->givenThereIsASubscription();

        $searchIntegration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($searchIntegration);

        $organizationId = Uuid::uuid4();
        $this->integrationRepository->activateWithOrganization($integrationId, $organizationId, null);

        $this->assertDatabaseHas('integrations', [
            'id' => $searchIntegration->id->toString(),
            'type' => $searchIntegration->type,
            'name' => $searchIntegration->name,
            'description' => $searchIntegration->description,
            'subscription_id' => $searchIntegration->subscriptionId,
            'organization_id' => $organizationId,
            'status' => IntegrationStatus::Active,
        ]);

        Event::assertDispatched(IntegrationActivated::class);
    }

    public function test_it_can_activate_with_organization_and_coupon(): void
    {
        $couponId = uuid::uuid4();
        $couponCode = '123';
        CouponModel::query()->insert([
            'id' => $couponId->toString(),
            'is_distributed' => false,
            'integration_id' => null,
            'code' => $couponCode,
        ]);

        $integrationId = Uuid::uuid4();

        $subscription = $this->givenThereIsASubscription();

        $searchIntegration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($searchIntegration);

        $organizationId = Uuid::uuid4();
        $this->integrationRepository->activateWithOrganization($integrationId, $organizationId, $couponCode);

        $this->assertDatabaseHas('integrations', [
            'id' => $searchIntegration->id->toString(),
            'type' => $searchIntegration->type,
            'name' => $searchIntegration->name,
            'description' => $searchIntegration->description,
            'subscription_id' => $searchIntegration->subscriptionId,
            'organization_id' => $organizationId,
            'status' => IntegrationStatus::Active,
        ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $couponId->toString(),
            'is_distributed' => true,
            'integration_id' => $searchIntegration->id->toString(),
            'code' => $couponCode,
        ]);

        Event::assertDispatched(IntegrationActivated::class);
    }

    public function test_it_will_fail_on_a_used_coupon(): void
    {
        $couponId = uuid::uuid4();
        $couponCode = '123';

        $integrationId = Uuid::uuid4();

        CouponModel::query()->insert([
            'id' => $couponId->toString(),
            'is_distributed' => true,
            'integration_id' => Uuid::uuid4(),
            'code' => $couponCode,
        ]);

        $subscription = $this->givenThereIsASubscription();

        $searchIntegration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->save($searchIntegration);

        $this->expectException(ModelNotFoundException::class);

        $this->integrationRepository->activateWithOrganization($integrationId, Uuid::uuid4(), $couponCode);
    }

    public function test_it_can_save_partner_status(): void
    {
        $integrationId = Uuid::uuid4();

        $subscription = $this->givenThereIsASubscription();

        $searchIntegration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'First party integration',
            'First party integration',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::FIRST_PARTY,
        );

        $this->integrationRepository->save($searchIntegration);

        $this->assertDatabaseHas('integrations', [
            'id' => $integrationId,
            'name' => 'First party integration',
            'partner_status' => IntegrationPartnerStatus::FIRST_PARTY,
        ]);
    }

    // https://jira.publiq.be/browse/PPF-555
    public function test_it_can_save_integration_uitpas_always_with_key_visibility_v2(): void
    {
        Event::fakeExcept([
            'eloquent.creating: ' . IntegrationModel::class,
        ]);

        $integrationId = Uuid::uuid4();

        $subscription = $this->givenThereIsASubscription(
            category: SubscriptionCategory::Free,
            integrationType: IntegrationType::UiTPAS
        );

        $integration = new Integration(
            $integrationId,
            IntegrationType::UiTPAS,
            'First party integration',
            'First party integration',
            $subscription->id,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::FIRST_PARTY,
        );
        $integration = $integration->withKeyVisibility(KeyVisibility::v1);

        $this->integrationRepository->save($integration);

        $this->assertDatabaseHas('integrations', [
            'id' => $integrationId,
            'key_visibility' => 'v2',
        ]);
    }

    public function test_it_can_not_save_integration_with_subscription_with_different_integration_type(): void
    {

        $subscriptionId = Uuid::uuid4();

        $searchSubscription = new Subscription(
            $subscriptionId,
            'Basic Plan',
            'Basic Plan description',
            SubscriptionCategory::Basic,
            IntegrationType::SearchApi,
            Currency::EUR,
            14.99,
            99.99
        );

        $this->subscriptionRepository->save($searchSubscription);

        $uitpasIntegration = new Integration(
            Uuid::uuid4(),
            IntegrationType::UiTPAS,
            'First party integration',
            'First party integration',
            $subscriptionId,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::FIRST_PARTY,
        );

        $this->subscriptionRepository->save($searchSubscription);

        $uitpasIntegration = $uitpasIntegration->withSubscription($searchSubscription);

        $this->assertThrows(
            fn () => $this->integrationRepository->save($uitpasIntegration),
            InconsistentIntegrationType::class
        );
    }

    public function test_get_drafts_by_type_and_between_months_old(): void
    {
        $this->setUpDatabaseForGetDraftsByTypeAndBetweenMonthsOld();

        $actual = $this->integrationRepository->getDraftsByTypeAndBetweenMonthsOld(
            IntegrationType::SearchApi,
            12,
            24,
            TemplateName::INTEGRATION_ACTIVATION_REMINDER,
        );

        $this->assertEqualsCanonicalizing(
            [
                'A different type of email has been sent, should be selected',
                'Should be selected!',
                'Should also be selected!',
            ],
            $actual->map(fn ($item) => $item->name)->toArray()
        );
    }

    private function setUpDatabaseForGetDraftsByTypeAndBetweenMonthsOld(): void
    {
        $wrongTypeId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $wrongTypeId,
            'type' => IntegrationType::EntryApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should not be selected: wrong type',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(14),
        ]);
        $this->setUpContact($wrongTypeId);

        $alreadyActiveId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $alreadyActiveId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should not be selected: already active',
            'description' => 'test',
            'status' => IntegrationStatus::Active,
            'created_at' => Carbon::now()->subMonths(14),
        ]);
        $this->setUpContact($alreadyActiveId);

        $noContactsId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $noContactsId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should not be selected: No contacts',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(14),
        ]);

        $createdToRecentlyId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $createdToRecentlyId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should not be selected: Created too recently',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(11),
        ]);
        $this->setUpContact($createdToRecentlyId);

        $mailAlreadySentId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $mailAlreadySentId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should not be selected: Created too recently',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(11),
        ]);
        DB::table('integrations_mails')->insert([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $mailAlreadySentId,
            'template_name' => TemplateName::INTEGRATION_ACTIVATION_REMINDER->value,
        ]);
        $this->setUpContact($mailAlreadySentId);

        $tooOldId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $tooOldId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should not be selected: Too old',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(50),
        ]);
        $this->setUpContact($tooOldId);

        $hasAdminHoldId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $hasAdminHoldId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should not be selected: has an admin hold state',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(14),
        ]);
        $this->setUpContact($hasAdminHoldId);
        DB::table('admin_information')->insert([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $hasAdminHoldId,
            'on_hold' => true,
            'comment' => 'Integration is on hold',
        ]);

        $shouldBeSelectedId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $shouldBeSelectedId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should be selected!',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(14),
        ]);
        $this->setUpContact($shouldBeSelectedId);

        $shouldAlsoBeSelectedId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $shouldAlsoBeSelectedId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Should also be selected!',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(15),
        ]);
        $this->setUpContact($shouldAlsoBeSelectedId);

        $differentTypeOfMailId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $differentTypeOfMailId,
            'type' => IntegrationType::SearchApi,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'A different type of email has been sent, should be selected',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(15),
        ]);
        DB::table('integrations_mails')->insert([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $differentTypeOfMailId,
            'template_name' => TemplateName::INTEGRATION_CREATED->value,
        ]);
        $this->setUpContact($differentTypeOfMailId);
    }

    private function setUpContact(string $integrationId): void
    {
        DB::table('contacts')->insert([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $integrationId,
            'email' => 'grote.smurf@example.com',
            'type' => ContactType::Technical->value,
            'first_name' => 'Grote',
            'last_name' => 'Smurf',
        ]);
    }

    private function givenThereIsASubscription(
        ?UuidInterface $id = null,
        ?string $name = null,
        ?string $description = null,
        ?SubscriptionCategory $category = null,
        ?IntegrationType $integrationType = null,
        ?Currency $currency = null,
        ?float $price = null,
        ?float $fee = null
    ): Subscription {
        $subscription = new Subscription(
            $id ?? Uuid::uuid4(),
            $name ?? 'Mock Subscription',
            $description ?? 'Mock description',
            $category ?? SubscriptionCategory::Basic,
            $integrationType ?? IntegrationType::SearchApi,
            $currency ?? Currency::EUR,
            $price ?? 100.0,
            $fee ?? 50.0
        );
        $this->subscriptionRepository->save($subscription);
        return $subscription;
    }
}
