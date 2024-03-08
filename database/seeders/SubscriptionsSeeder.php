<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Domain\Subscriptions\SubscriptionPlan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

final class SubscriptionsSeeder extends Seeder
{
    private function getSubscription(SubscriptionPlan $subscriptionPlan): Subscription
    {
        $subscriptionId = Uuid::fromString($subscriptionPlan->value);

        return match ($subscriptionPlan) {
            SubscriptionPlan::FREE_ENTRY_API_PLAN => new Subscription(
                $subscriptionId,
                'Free Plan - Entry API',
                'Free Plan for integrating with Entry API',
                SubscriptionCategory::Free,
                IntegrationType::EntryApi,
                Currency::EUR,
                0,
                0
            ),
            SubscriptionPlan::BASIC_SEARCH_API_PLAN => new Subscription(
                $subscriptionId,
                'Basic Plan - Search API - Monthly',
                'Basic Plan for integrating with Search API, billed monthly',
                SubscriptionCategory::Basic,
                IntegrationType::SearchApi,
                Currency::EUR,
                125,
                0
            ),
            SubscriptionPlan::CUSTOM_SEARCH_API_PLAN => new Subscription(
                $subscriptionId,
                'Custom Plan - Search API - Monthly',
                'Custom Plan for integrating with Search API, billed monthly and with a onetime fee.',
                SubscriptionCategory::Custom,
                IntegrationType::SearchApi,
                Currency::EUR,
                0,
                0
            ),
            SubscriptionPlan::BASIC_WIDGETS_PLAN => new Subscription(
                $subscriptionId,
                'Basic Plan - Widgets - Monthly',
                'Basic Plan for integrating Widgets, billed monthly.',
                SubscriptionCategory::Basic,
                IntegrationType::Widgets,
                Currency::EUR,
                125,
                0
            ),
            SubscriptionPlan::PLUS_WIDGETS_PLAN => new Subscription(
                $subscriptionId,
                'Plus Plan - Widgets - Monthly',
                'Plus Plan for integrating with Widgets, billed monthly and with a onetime fee.',
                SubscriptionCategory::Plus,
                IntegrationType::Widgets,
                Currency::EUR,
                280,
                600
            ),
            SubscriptionPlan::CUSTOM_WIDGETS_PLAN => new Subscription(
                $subscriptionId,
                'Custom Plan - Widgets - Monthly',
                'Custom Plan for integrating with Widgets, billed monthly and with a onetime fee.',
                SubscriptionCategory::Custom,
                IntegrationType::Widgets,
                Currency::EUR,
                0,
                0
            ),
        };
    }

    public function run(SubscriptionRepository $subscriptionRepository): void
    {
        foreach (SubscriptionPlan::cases() as $subscriptionPlan) {
            $subscriptionId = Uuid::fromString($subscriptionPlan->value);

            try {
                $subscriptionRepository->getById($subscriptionId);
                $this->command->info('Subscription already exists');
                continue;
            } catch (ModelNotFoundException) {
            }

            $subscription = $this->getSubscription($subscriptionPlan);
            $subscriptionRepository->save($subscription);
        }
    }
}
