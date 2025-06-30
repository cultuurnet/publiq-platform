<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\RequestActivationRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUdbOrganizersRequest;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class UdbOrganizerMapper
{
    /**
     * @return UdbOrganizer[]
     */
    public static function map(Request $request, string $id): array
    {
        /**
         * @var UdbOrganizer[] $organizers
         */
        $organizers = [];

        foreach ($request->input('organizers') ?? [] as $organizer) {
            $organizers[] = new UdbOrganizer(
                Uuid::uuid4(),
                Uuid::fromString($id),
                new UdbUuid($organizer['id']),
                UdbOrganizerStatus::Pending
            );
        }

        return $organizers;
    }

    public static function mapUpdateOrganizers(UpdateIntegrationUdbOrganizersRequest $request, string $id): array
    {
        return self::map($request, $id);
    }

    public static function mapActivationRequest(RequestActivationRequest $request, string $id): UdbOrganizers
    {
        return new UdbOrganizers(self::map($request, $id));
    }
}
