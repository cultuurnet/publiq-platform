<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Activity\Policies\ActivityPolicy;
use App\Domain\Auth\Controllers\CallbackController;
use App\Domain\Auth\Controllers\LoginController;
use App\Domain\Auth\UserProvider;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Contacts\Policies\ContactPolicy;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Coupons\Policies\CouponPolicy;
use App\Domain\Integrations\Models\AdminInformationModel;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Models\IntegrationUrlModel;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Policies\AdminInformationPolicy;
use App\Domain\Integrations\Policies\IntegrationPolicy;
use App\Domain\Integrations\Policies\IntegrationUrlPolicy;
use App\Domain\Integrations\Policies\UdbOrganizerPolicy;
use App\Domain\KeyVisibilityUpgrades\Models\KeyVisibilityUpgradeModel;
use App\Domain\KeyVisibilityUpgrades\Policies\KeyVisibilityUpgradePolicy;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Policies\OrganizationPolicy;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Domain\Subscriptions\Policies\SubscriptionPolicy;
use App\Keycloak\KeycloakConfig;
use App\Keycloak\Models\KeycloakClientModel;
use App\Keycloak\Policies\KeycloakClientPolicy;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\Policies\UiTiDv1ConsumerPolicy;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

final class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        AdminInformationModel::class => AdminInformationPolicy::class,
        ContactModel::class => ContactPolicy::class,
        CouponModel::class => CouponPolicy::class,
        IntegrationModel::class => IntegrationPolicy::class,
        IntegrationUrlModel::class => IntegrationUrlPolicy::class,
        OrganizationModel::class => OrganizationPolicy::class,
        UdbOrganizerModel::class => UdbOrganizerPolicy::class,
        SubscriptionModel::class => SubscriptionPolicy::class,
        UiTiDv1ConsumerModel::class => UiTiDv1ConsumerPolicy::class,
        KeycloakClientModel::class => KeycloakClientPolicy::class,
        KeyVisibilityUpgradeModel::class => KeyVisibilityUpgradePolicy::class,
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
            static function (): Auth0 {
                return new Auth0(new SdkConfiguration(config('keycloak.login')));
            }
        );

        $oAuthLoginParameters = [];
        parse_str(config(KeycloakConfig::KEYCLOAK_LOGIN_PARAMETERS), $oAuthLoginParameters);

        $this->app->when(LoginController::class)
            ->needs('$loginParams')
            ->give($oAuthLoginParameters);

        $this->app->when(CallbackController::class)
            ->needs('$loginParams')
            ->give($oAuthLoginParameters);
    }
}
