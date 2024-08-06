<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\RequestActivationRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUiTdatabankOrganizersRequest;
use App\Domain\Integrations\UdbOrganizer;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class UiTdatabankOrganizerMapper
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
                $organizer['id']
            );
        }

        return $organizers;
    }

    public static function mapUpdateOrganizers(UpdateIntegrationUiTdatabankOrganizersRequest $request, string $id): array
    {
        return self::map($request, $id);
    }

    public static function mapActivationRequest(RequestActivationRequest $request, string $id): array
    {
        return self::map($request, $id);
    }
}
