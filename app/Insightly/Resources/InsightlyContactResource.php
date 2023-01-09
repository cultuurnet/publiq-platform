<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\Contact;
use App\Insightly\InsightlyClient;
use App\Insightly\Serializers\ContactSerializer;
use App\Json;
use GuzzleHttp\Psr7\Request;

final class InsightlyContactResource implements ContactResource
{
    private string $path = 'Contacts/';

    public function __construct(private readonly InsightlyClient $insightlyClient)
    {
    }

    public function create(Contact $contact): int
    {
        $request = new Request(
            'POST',
            $this->path,
            [],
            Json::encode((new ContactSerializer())->toInsightlyArray($contact))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $contactAsArray = Json::decodeAssociatively($response->getBody()->getContents());

        return $contactAsArray['CONTACT_ID'];
    }

    public function update(Contact $contact, int $insightlyId): void
    {
        $request = new Request(
            'PUT',
            $this->path,
            [],
            Json::encode((new ContactSerializer())->toInsightlyArrayForUpdate($contact, $insightlyId))
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function delete(int $id): void
    {
        $request = new Request(
            'DELETE',
            $this->path . $id
        );

        $this->insightlyClient->sendRequest($request);
    }
}
