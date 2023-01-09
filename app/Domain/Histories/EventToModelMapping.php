<?php

declare(strict_types=1);

namespace App\Domain\Histories;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Domain\Organizations\Models\OrganizationModel;

final class EventToModelMapping
{
    public const MAPPING = [
            ContactCreated::class => ContactModel::class,
            IntegrationCreated::class => IntegrationModel::class,
            OrganizationCreated::class => OrganizationModel::class,
            OrganizationUpdated::class => OrganizationModel::class,
            OrganizationDeleted::class => OrganizationModel::class,
        ];
}
