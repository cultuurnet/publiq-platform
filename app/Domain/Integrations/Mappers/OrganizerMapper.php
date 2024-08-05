<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\RequestActivationRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationOrganizersRequest;
use App\Domain\Integrations\UiTdatabankOrganizer;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class OrganizerMapper
{
    /**
     * @return UiTdatabankOrganizer[]
     */
    public static function map(Request $request, string $id): array
    {
        /**
         * @var UiTdatabankOrganizer[] $organizers
         */
        $organizers = [];

        foreach ($request->input('organizers') ?? [] as $organizer) {
            $organizers[] = new UiTdatabankOrganizer(
                Uuid::uuid4(),
                Uuid::fromString($id),
                $organizer['id']
            );
        }

        return $organizers;
    }

    public static function mapUpdateOrganizers(UpdateIntegrationOrganizersRequest $request, string $id): array
    {
        return self::map($request, $id);
    }

    public static function mapActivationRequest(RequestActivationRequest $request, string $id): array
    {
        return self::map($request, $id);
    }
}
