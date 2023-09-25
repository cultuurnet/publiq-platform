<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\UpdateIntegrationUrlsRequest;
use App\Domain\Integrations\IntegrationUrl;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

final class UpdateIntegrationUrlsMapper
{
    /**
     * @param array<IntegrationUrl> $currentIntegrationUrls
     * @return array<IntegrationUrl>
     */
    public static function map(UpdateIntegrationUrlsRequest $request, array $currentIntegrationUrls): array
    {
        $loginUrls = $request->input('loginUrls') ?? [];
        $callbackUrls = $request->input('callbackUrls') ?? [];
        $logoutUrls = $request->input('logoutUrls') ?? [];

        $changedUrls = [
            ...$loginUrls,
            ...$callbackUrls,
            ...$logoutUrls,
        ];

        $updated = [];

        $currentUrlsCollection = Collection::make($currentIntegrationUrls);

        foreach ($changedUrls as $changedUrl) {
            $currentUrl = $currentUrlsCollection->firstOrFail(
                fn (IntegrationUrl $currentUrl, int $index) => $currentUrl->id->equals(Uuid::fromString($changedUrl['id']))
            );

            $updated[] = new IntegrationUrl(
                $currentUrl->id,
                $currentUrl->integrationId,
                $currentUrl->environment,
                $currentUrl->type,
                $changedUrl->url
            );
        }

        return $updated;
    }
}
