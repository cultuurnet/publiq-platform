<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\FormRequests\RequestActivationRequest;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUdbOrganizersRequest;
use App\Domain\Integrations\Integration;
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
    public static function map(Request $request, Integration $integration): array
    {
        /**
         * @var UdbOrganizer[] $organizers
         */
        $organizers = [];

        $productionClient = $integration->getKeycloakClientByEnv(Environment::Production);

        foreach ($request->input('organizers') ?? [] as $organizer) {
            $organizers[] = new UdbOrganizer(
                Uuid::uuid4(),
                $integration->id,
                new UdbUuid($organizer['id']),
                UdbOrganizerStatus::Pending,
                $productionClient->id
            );
        }

        return $organizers;
    }

    public static function mapUpdateOrganizers(UpdateIntegrationUdbOrganizersRequest $request, Integration $integration): array
    {
        return self::map($request, $integration);
    }

    public static function mapActivationRequest(RequestActivationRequest $request, Integration $integration): UdbOrganizers
    {
        return new UdbOrganizers(self::map($request, $integration));
    }
}
