<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Json;
use GuzzleHttp\Psr7\Request;

trait InsightlyLinks
{
    private function getLinksForResource(string $path, int $resourceId): array
    {
        $getLinksRequest = new Request(
            'GET',
            $path . $resourceId . '/Links',
        );
        $getLinksResponse = $this->insightlyClient->sendRequest($getLinksRequest);

        return Json::decodeAssociatively($getLinksResponse->getBody()->getContents());
    }

    private function getLinkIdForContact(array $links, int $resourceId, int $contactId): ?int
    {
        foreach ($links as $link) {
            $objectId = $link['OBJECT_ID'];
            $linkName = $link['LINK_OBJECT_NAME'];
            $linkObjectId = $link['LINK_OBJECT_ID'];

            if ($objectId === $resourceId && $linkName === 'Contact' && $linkObjectId === $contactId) {
                return (int) $link['LINK_ID'];
            }
        }

        return null;
    }
}
