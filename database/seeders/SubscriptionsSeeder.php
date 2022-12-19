<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

final class SubscriptionsSeeder extends Seeder
{
    public function run(SubscriptionRepository $subscriptionRepository): void
    {
        $subscriptionId = Uuid::fromString('b46745a1-feb5-45fd-8fa9-8e3ef25aac26');

        try {
            $subscriptionRepository->getById($subscriptionId);
            $this->command->info('Subscription already exists');
            return;
        } catch (ModelNotFoundException) {
        }

        $subscription = new Subscription(
            $subscriptionId,
            'Basic Plan - Search API - Monthly',
            'Basic Plan for integrating with Search API, billed monthly and with a onetime fee.',
            IntegrationType::SearchApi,
            Currency::EUR,
            999,
            1499
        );

        $subscriptionRepository->save($subscription);
    }
}
