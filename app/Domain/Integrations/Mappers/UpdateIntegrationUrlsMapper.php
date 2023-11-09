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
        $currentUrlsCollection = Collection::make($currentIntegrationUrls);

        $changedUrlsCollection = Collection::make(
            [
                ...($request->input('loginUrls') ?? []),
                ...($request->input('callbackUrls') ?? []),
                ...($request->input('logoutUrls') ?? []),
            ]
        )->filter(fn ($val) => $val !== null)->values();

        return $changedUrlsCollection->map(
            function (array $changedUrl) use ($currentUrlsCollection) {
                $currentUrl = $currentUrlsCollection->firstOrFail(
                    fn (IntegrationUrl $currentUrl) => $currentUrl->id->equals(
                        Uuid::fromString($changedUrl['id'])
                    )
                );

                return new IntegrationUrl(
                    $currentUrl->id,
                    $currentUrl->integrationId,
                    $currentUrl->environment,
                    $currentUrl->type,
                    $changedUrl['url']
                );
            }
        )->toArray();
    }
}
