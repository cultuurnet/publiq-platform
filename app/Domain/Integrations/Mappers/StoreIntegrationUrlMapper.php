<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\FormRequests\StoreIntegrationUrlRequest;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use Ramsey\Uuid\Uuid;

final class StoreIntegrationUrlMapper
{
    public static function map(StoreIntegrationUrlRequest $request, string $integrationId): IntegrationUrl
    {
        return new IntegrationUrl(
            Uuid::uuid4(),
            Uuid::fromString($integrationId),
            Environment::from($request->input('environment')),
            IntegrationUrlType::from($request->input('type')),
            $request->input('url')
        );
    }
}
