<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUrlsRequest;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

final class UpdateIntegrationUrlsMapper
{
    /**
     * @param Collection<IntegrationUrl> $currentIntegrationUrls
     * @return Collection<IntegrationUrl>
     */
    public static function map(UpdateIntegrationUrlsRequest $request, Collection $currentIntegrationUrls): Collection
    {
        $changedUrls = Collection::make(
            [
                ...($request->input('loginUrls') ?? []),
                ...($request->input('callbackUrls') ?? []),
                ...($request->input('logoutUrls') ?? []),
            ]
        )->filter(fn ($val) => $val !== null)->values();

        return $changedUrls->map(
            function (array $changedUrl) use ($currentIntegrationUrls) {
                /** @var ?IntegrationUrl $currentUrl */
                $currentUrl = null;

                if ($changedUrl['id']) {
                    $currentUrl = $currentIntegrationUrls->first(
                        fn (IntegrationUrl $currentUrl) => $currentUrl->id->equals(
                            Uuid::fromString($changedUrl['id'])
                        )
                    );
                }

                if ($currentUrl === null) {
                    return new IntegrationUrl(
                        Uuid::uuid4(),
                        Uuid::fromString($changedUrl['integrationId']),
                        Environment::from($changedUrl['environment']),
                        IntegrationUrlType::from($changedUrl['type']),
                        $changedUrl['url']
                    );
                }

                return new IntegrationUrl(
                    $currentUrl->id,
                    $currentUrl->integrationId,
                    $currentUrl->environment,
                    $currentUrl->type,
                    $changedUrl['url']
                );
            }
        );
    }
}
