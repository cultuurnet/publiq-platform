<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\BillingInterval;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

final class SubscriptionsSeeder extends Seeder
{
    public function run(SubscriptionRepository $subscriptionRepository): void
    {
        $subscription = new Subscription(
            Uuid::uuid4(),
            'Basic Plan - Search API - Monthly',
            'Basic Plan for integrating with Search API, billed monthly and with a onetime fee.',
            IntegrationType::SearchApi,
            Currency::EUR,
            999,
            BillingInterval::Monthly,
            1499
        );

        $subscriptionRepository->save($subscription);
    }
}
