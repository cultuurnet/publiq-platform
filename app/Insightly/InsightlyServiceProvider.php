<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Insightly\Listeners\ActivateProject;
use App\Insightly\Listeners\BlockOpportunity;
use App\Insightly\Listeners\BlockProject;
use App\Insightly\Listeners\CreateOpportunity;
use App\Insightly\Listeners\CreateOrganization;
use App\Insightly\Listeners\CreateProjectWithOrganization;
use App\Insightly\Listeners\DeleteOpportunity;
use App\Insightly\Listeners\DeleteProject;
use App\Insightly\Listeners\SyncContact;
use App\Insightly\Listeners\DeleteOrganization;
use App\Insightly\Listeners\UpdateOpportunity;
use App\Insightly\Listeners\UpdateOrganization;
use App\Insightly\Listeners\UpdateProject;
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

        if (config('insightly.enabled')) {
            Event::listen(IntegrationCreated::class, [CreateOpportunity::class, 'handle']);

            Event::listen(IntegrationActivationRequested::class, [ActivateProject::class, 'handle']);
            Event::listen(IntegrationActivated::class, [ActivateProject::class, 'handle']);

            Event::listen(IntegrationBlocked::class, [BlockProject::class, 'handle']);
            Event::listen(IntegrationBlocked::class, [BlockOpportunity::class, 'handle']);

            Event::listen(IntegrationUpdated::class, [UpdateOpportunity::class, 'handle']);
            Event::listen(IntegrationUpdated::class, [UpdateProject::class, 'handle']);

            Event::listen(IntegrationDeleted::class, [DeleteOpportunity::class, 'handle']);
            Event::listen(IntegrationDeleted::class, [DeleteProject::class, 'handle']);

            Event::listen(ContactCreated::class, [SyncContact::class, 'handleContactCreated']);
            Event::listen(ContactUpdated::class, [SyncContact::class, 'handleContactUpdated']);

            Event::listen(OrganizationCreated::class, [CreateOrganization::class, 'handle']);
            Event::listen(OrganizationUpdated::class, [UpdateOrganization::class, 'handle']);
            Event::listen(OrganizationDeleted::class, [DeleteOrganization::class, 'handle']);
        }
    }

    public function boot(): void
    {
    }
}
