<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Insightly\Listeners\CreateContact;
use App\Insightly\Listeners\CreateOpportunity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

final class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            IntegrationCreated::class,
            [CreateOpportunity::class, 'handle']
        );

        Event::listen(
            ContactCreated::class,
            [CreateContact::class, 'handle']
        );
    }
}
