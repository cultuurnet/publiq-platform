<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\BillingInterval;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class IntegrationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private IntegrationRepository $integrationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = new IntegrationRepository();
    }

    public function test_it_can_save_an_integration(): void
    {
        $subscription = new Subscription(
            Uuid::uuid4(),
            'Basic Plan',
            'Basic Plan description',
            Currency::EUR,
            999,
            BillingInterval::Monthly,
            1499
        );

        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            $subscription,
            [
                new Contact(
                    Uuid::uuid4(),
                    ContactType::Technical,
                    'Jane',
                    'Doe',
                    'jane.doe@anonymous.com'
                ),
            ]
        );

        $this->integrationRepository->save($integration);

        $this->assertDatabaseHas('integration', [
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $integration->subscription->id->toString(),
        ]);
    }
}
