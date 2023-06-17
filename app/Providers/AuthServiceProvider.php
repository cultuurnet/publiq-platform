<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Activity\Policies\ActivityPolicy;
use App\Domain\Auth\Controllers\Login;
use App\Domain\Auth\UserProvider;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Contacts\Policies\ContactPolicy;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Coupons\Policies\CouponPolicy;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Models\IntegrationUrlModel;
use App\Domain\Integrations\Policies\IntegrationPolicy;
use App\Domain\Integrations\Policies\IntegrationUrlPolicy;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Policies\OrganizationPolicy;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Domain\Subscriptions\Policies\SubscriptionPolicy;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

final class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        ContactModel::class => ContactPolicy::class,
        CouponModel::class => CouponPolicy::class,
        IntegrationModel::class => IntegrationPolicy::class,
        IntegrationUrlModel::class => IntegrationUrlPolicy::class,
        OrganizationModel::class => OrganizationPolicy::class,
        SubscriptionModel::class => SubscriptionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Auth::provider(
            'auth0',
            fn ($app, array $config) => new UserProvider()
        );

        $this->app->singleton(
            Auth0::class,
            static fn (): Auth0 => new Auth0(new SdkConfiguration(config('auth0')))
        );

        $auth0LoginParameters = [];
        parse_str(config('auth0.login_parameters'), $auth0LoginParameters);

        $this->app->when(Login::class)
            ->needs('$loginParams')
            ->give($auth0LoginParameters);
    }
}
