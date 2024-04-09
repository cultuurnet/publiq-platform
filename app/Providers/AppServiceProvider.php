<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Contacts\Repositories\ContactKeyVisibilityRepository;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Contacts\Repositories\EloquentContactKeyVisibilityRepository;
use App\Domain\Contacts\Repositories\EloquentContactRepository;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Coupons\Repositories\EloquentCouponRepository;
use App\Domain\KeyVisibilityUpgrades\Repositories\EloquentKeyVisibilityUpgradeRepository;
use App\Domain\KeyVisibilityUpgrades\Repositories\KeyVisibilityUpgradeRepository;
use App\Domain\Organizations\Repositories\EloquentOrganizationRepository;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Domain\Subscriptions\Repositories\EloquentSubscriptionRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContactRepository::class, EloquentContactRepository::class);
        $this->app->bind(ContactKeyVisibilityRepository::class, EloquentContactKeyVisibilityRepository::class);
        $this->app->bind(CouponRepository::class, EloquentCouponRepository::class);
        $this->app->bind(OrganizationRepository::class, EloquentOrganizationRepository::class);
        $this->app->bind(SubscriptionRepository::class, EloquentSubscriptionRepository::class);
        $this->app->bind(KeyVisibilityUpgradeRepository::class, EloquentKeyVisibilityUpgradeRepository::class);
    }
}
