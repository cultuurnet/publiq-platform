<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Json;
use GuzzleHttp\Psr7\Request;

trait InsightlyLinks
{
    private function getLinks(int $id): array
    {
        $getLinksRequest = new Request(
            'GET',
            $this->path . $id . '/Links',
        );
        $getLinksResponse = $this->insightlyClient->sendRequest($getLinksRequest);

        return Json::decodeAssociatively($getLinksResponse->getBody()->getContents());
    }

    private function getLink(int $id, int $linkedId, ResourceType $resourceType): ?int
    {
        $links = $this->getLinks($id);

        foreach ($links as $link) {
            $objectId = $link['OBJECT_ID'];
            $linkName = $link['LINK_OBJECT_NAME'];
            $linkObjectId = $link['LINK_OBJECT_ID'];

            if ($objectId === $id && $linkName === $resourceType->name && $linkObjectId === $linkedId) {
                return (int) $link['LINK_ID'];
            }
        }

        return null;
    }
}
