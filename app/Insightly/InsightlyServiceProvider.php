<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Events\ContactDeleted;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Insightly\Listeners\CreateContact;
use App\Insightly\Listeners\CreateOpportunity;
use App\Insightly\Listeners\CreateOrganization;
use App\Insightly\Listeners\UnlinkContact;
use App\Insightly\Listeners\UpdateContact;
use App\Insightly\Listeners\DeleteOrganization;
use App\Insightly\Listeners\UpdateOrganization;
use App\Insightly\Repositories\EloquentInsightlyMappingRepository;
use App\Insightly\Repositories\InsightlyMappingRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class InsightlyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InsightlyMappingRepository::class, EloquentInsightlyMappingRepository::class);
        $this->app->bind(ContactLink::class, InsightlyContactLink::class);

        $this->app->singleton(InsightlyClient::class, function () {
            return new HttpInsightlyClient(
                new Client(
                    [
                        'base_uri' => config('insightly.host'),
                        'http_errors' => false,
                    ]
                ),
                config('insightly.api_key') ?? '',
                new Pipelines(config('insightly.pipelines'))
            );
        });

        if (!empty(config('insightly.api_key'))) {
            Event::listen(IntegrationCreated::class, [CreateOpportunity::class, 'handle']);
            Event::listen(ContactCreated::class, [CreateContact::class, 'handle']);
            Event::listen(ContactDeleted::class, [UnlinkContact::class, 'handle']);
            Event::listen(OrganizationCreated::class, [CreateOrganization::class, 'handle']);
            Event::listen(OrganizationUpdated::class, [UpdateOrganization::class, 'handle']);
            Event::listen(ContactUpdated::class, [UpdateContact::class, 'handle']);
            Event::listen(OrganizationDeleted::class, [DeleteOrganization::class, 'handle']);
        }
    }

    public function boot(): void
    {
    }
}
