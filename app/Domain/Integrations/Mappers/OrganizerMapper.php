<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\RequestActivationRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationOrganizersRequest;
use App\Domain\Integrations\Organizer;
use Ramsey\Uuid\Uuid;

final class OrganizerMapper
{
    /**
     * @return Organizer[]
     */
    public static function map(RequestActivationRequest|UpdateIntegrationOrganizersRequest $request, string $id): array
    {
        /**
         * @var Organizer[] $organizers
         */
        $organizers = [];

        foreach ($request->input('organizers') ?? [] as $organizer) {
            $organizers[] = new Organizer(
                Uuid::uuid4(),
                Uuid::fromString($id),
                Uuid::fromString($organizer['id'])
            );
        }

        return $organizers;
    }
}
