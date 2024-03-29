<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\Contact;
use App\Insightly\InsightlyClient;
use App\Insightly\Objects\InsightlyContact;
use App\Insightly\Objects\InsightlyContacts;
use App\Insightly\Serializers\ContactSerializer;
use App\Insightly\Serializers\LinkSerializer;
use App\Json;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Arr;

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

    public function get(int $id): array
    {
        $request = new Request(
            'GET',
            $this->path . $id
        );

        $response = $this->insightlyClient->sendRequest($request);

        return Json::decodeAssociatively($response->getBody()->getContents());
    }

    public function update(Contact $contact, int $id): void
    {
        $request = new Request(
            'PUT',
            $this->path,
            [],
            Json::encode((new ContactSerializer())->toInsightlyArrayForUpdate($contact, $id))
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

    public function findByEmail(string $email): InsightlyContacts
    {
        $request = new Request(
            'GET',
            $this->path . "Search/?field_name=EMAIL_ADDRESS&field_value=$email"
        );

        $response = $this->insightlyClient->sendRequest($request);

        $foundContacts = Json::decodeAssociatively($response->getBody()->getContents());

        return new InsightlyContacts(
            Arr::map(
                $foundContacts,
                static fn (array $contact) => new InsightlyContact((int) $contact['CONTACT_ID'], count($contact['LINKS']))
            )
        );
    }

    public function linkContact(int $id, int $relatedId): void
    {
        $request = new Request(
            'POST',
            $this->path . $id . '/Links',
            [],
            Json::encode((new LinkSerializer())->contactToContactLink($relatedId))
        );

        $this->insightlyClient->sendRequest($request);
    }
}
