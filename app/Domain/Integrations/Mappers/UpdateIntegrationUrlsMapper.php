<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\FormRequests\UpdateIntegrationUrlsRequest;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UpdateIntegrationUrlsMapper
{
    /**
     * @param Collection<IntegrationUrl> $currentIntegrationUrls
     * @return Collection<IntegrationUrl>
     */
    public static function map(UpdateIntegrationUrlsRequest $request, Collection $currentIntegrationUrls, UuidInterface $integrationId): Collection
    {
        /** @var array<array> $changedUrls */
        $changedUrls = $request->input('urls') ?? [];

        return collect($changedUrls)->map(
            function (array $changedUrl) use ($integrationId, $currentIntegrationUrls) {
                /** @var ?IntegrationUrl $currentUrl */
                $currentUrl = null;

                if (isset($changedUrl['id'])) {
                    $currentUrl = $currentIntegrationUrls->first(
                        fn (IntegrationUrl $currentUrl) => $currentUrl->id->equals(
                            Uuid::fromString($changedUrl['id'])
                        )
                    );
                }

                if ($currentUrl === null) {
                    return new IntegrationUrl(
                        Uuid::uuid4(),
                        $integrationId,
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
