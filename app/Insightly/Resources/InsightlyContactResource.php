<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\Contact;
use App\Insightly\InsightlyClient;
use App\Insightly\Models\InsightlyContact;
use App\Insightly\Serializers\ContactSerializer;
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

    /**
     * @return InsightlyContact[]
     */
    public function findByEmail(string $email): array
    {
        $request = new Request(
            'GET',
            $this->path . "Search/?field_name=EMAIL_ADDRESS&field_value=$email"
        );

        $response = $this->insightlyClient->sendRequest($request);

        $foundContacts = Json::decodeAssociatively($response->getBody()->getContents());

        return Arr::map(
            $foundContacts,
            fn (array $contact) => new InsightlyContact((int) $contact['CONTACT_ID'], count($contact['LINKS']))
        );
    }
}
