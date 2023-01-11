<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Activity\Policies\ActivityPolicy;
use App\Domain\Auth\Controllers\Login;
use App\Domain\Contacts\Models\ContactModel;
use App\Policies\ContactPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Activitylog\Models\Activity;

final class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        ContactModel::class => ContactPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        $auth0LoginParameters = [];
        parse_str(config('auth0.login_parameters'), $auth0LoginParameters);

        $this->app->when(Login::class)
            ->needs('$loginParams')
            ->give($auth0LoginParameters);
    }
}
