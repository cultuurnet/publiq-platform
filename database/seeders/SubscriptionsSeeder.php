<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

final class SubscriptionsSeeder extends Seeder
{
    private function getSubscription(SubscriptionUuid $subscriptionUuid): Subscription
    {
        $subscriptionId = Uuid::fromString($subscriptionUuid->value);

        return match ($subscriptionUuid) {
            SubscriptionUuid::FREE_ENTRY_API_PLAN => new Subscription(
                $subscriptionId,
                'Entry API - Free Plan',
                'Free Plan for integrating with Entry API',
                SubscriptionCategory::Free,
                IntegrationType::EntryApi,
                Currency::EUR,
                0,
                0
            ),
            SubscriptionUuid::BASIC_SEARCH_API_PLAN => new Subscription(
                $subscriptionId,
                'Search API - Basic Plan',
                'Basic Plan for integrating with Search API',
                SubscriptionCategory::Basic,
                IntegrationType::SearchApi,
                Currency::EUR,
                125,
                0
            ),
            SubscriptionUuid::CUSTOM_SEARCH_API_PLAN => new Subscription(
                $subscriptionId,
                'Search API - Custom',
                'Custom Plan for integrating with Search API',
                SubscriptionCategory::Custom,
                IntegrationType::SearchApi,
                Currency::EUR,
                0,
                0
            ),
            SubscriptionUuid::BASIC_WIDGETS_PLAN => new Subscription(
                $subscriptionId,
                'Widgets - Basic plan',
                'Basic Plan for integrating Widgets',
                SubscriptionCategory::Basic,
                IntegrationType::Widgets,
                Currency::EUR,
                125,
                0
            ),
            SubscriptionUuid::PLUS_WIDGETS_PLAN => new Subscription(
                $subscriptionId,
                'Widgets - Plus plan',
                'Plus Plan for integrating with Widgets',
                SubscriptionCategory::Plus,
                IntegrationType::Widgets,
                Currency::EUR,
                280,
                600
            ),
            SubscriptionUuid::CUSTOM_WIDGETS_PLAN => new Subscription(
                $subscriptionId,
                'Widgets - Custom plan',
                'Custom Plan for integrating with Widgets',
                SubscriptionCategory::Custom,
                IntegrationType::Widgets,
                Currency::EUR,
                0,
                0
            ),
            SubscriptionUuid::FREE_UITPAS_API_PLAN => new Subscription(
                $subscriptionId,
                'UiTPAS API - Free Plan',
                'Free Plan for integrating with UiTPAS API',
                SubscriptionCategory::Free,
                IntegrationType::UiTPAS,
                Currency::EUR,
                0,
                0
            ),
            SubscriptionUuid::UITNETWERK_SEARCH_API_PLAN => new Subscription(
                $subscriptionId,
                'Search API - UiTnetwerk Plan',
                'UiTnetwerk Plan for integrating with Search API',
                SubscriptionCategory::Uitnetwerk,
                IntegrationType::SearchApi,
                Currency::EUR,
                0,
                0
            ),
            SubscriptionUuid::UITNETWERK_WIDGETS_PLAN => new Subscription(
                $subscriptionId,
                'Widgets - UiTnetwerk Plan',
                'UiTnetwerk Plan for integrating with Widgets',
                SubscriptionCategory::Uitnetwerk,
                IntegrationType::Widgets,
                Currency::EUR,
                0,
                0
            ),
        };
    }

    private function isLegacySubscription(SubscriptionUuid $subscriptionUuid): bool
    {
        return in_array($subscriptionUuid, [SubscriptionUuid::PLUS_WIDGETS_PLAN]);
    }

    public function run(SubscriptionRepository $subscriptionRepository): void
    {
        foreach (SubscriptionUuid::cases() as $subscriptionUuid) {
            $subscription = $this->getSubscription($subscriptionUuid);
            try {
                $subscriptionRepository->getByIdWithTrashed($subscription->id);
            } catch (ModelNotFoundException) {
                $subscriptionRepository->save($subscription);
            }
            if ($this->isLegacySubscription($subscriptionUuid)) {
                $subscriptionRepository->deleteById($subscription->id);
            }
        }
    }
}
