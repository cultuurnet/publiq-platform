<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\Exceptions\RecordNotFound;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\UiTiDv1\UiTiDv1EnvironmentSDK;
use GuzzleHttp\ClientInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateUser extends Command
{
    protected $signature = 'migrate:user
                            {uitId : the UiTiD of the user to import as a contact}';

    protected $description = 'Migrate the user with the given UiTiD and import as a contact';

    private ClientInterface $oauthClient;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository
    ) {
        parent::__construct();

        $this->oauthClient = UiTiDv1EnvironmentSDK::createOAuth1HttpClient(
            config('uitidv1.environments.prod.baseUrl'),
            config('uitidv1.environments.prod.consumerKey'),
            config('uitidv1.environments.prod.consumerSecret')
        );
    }

    public function handle(): int
    {
        Model::unsetEventDispatcher();

        CauserResolver::setCauser(UserModel::createSystemUser());

        /** @var string $uitId */
        $uitId = $this->argument('uitId');

        if (!$this->confirm('Are you sure you want to import user with UiTiD ' . $uitId . '?')) {
            return 0;
        }

        $uitIdContact = $this->getContactFromUiTiD($uitId);
        $email = $uitIdContact?->email;
        if ($uitIdContact === null || $email === null) {
            $this->warn($uitId . ' - user not found inside UiTiD');
            return 1;
        }

        $insightlyContacts = $this->insightlyClient->contacts()->findByEmail($email);
        if (count($insightlyContacts) === 0) {
            $this->migrateContact($uitIdContact, 0);
            return 0;
        }

        $insightlyId = Arr::sort($insightlyContacts)[0];
        if (count($insightlyContacts) > 1) {
            $this->warn($uitId . ' - found multiple contacts with email ' . $email . ' used ' . $insightlyId);
        }

        $contact = $this->getContactFromInInsightly($uitId, (int) $insightlyId);
        if ($contact === null) {
            return 1;
        }

        $this->migrateContact($contact, $insightlyId);

        return 0;
    }

    private function migrateContact(Contact $contact, ?int $insightlyId): void
    {
        $this->info($contact->id . ' - importing user with email ' . $contact->email . ' and Insightly id ' . $insightlyId);
        $this->contactRepository->save($contact);

        if ($insightlyId !== null) {
            $insightlyMapping = new InsightlyMapping(
                $contact->id,
                $insightlyId,
                ResourceType::Contact
            );
            $this->insightlyMappingRepository->save($insightlyMapping);
        }
    }

    private function getContactFromUiTiD(string $uitId): ?Contact
    {
        $response = $this->oauthClient->request(
            'GET',
            'user/search?userId=' . $uitId,
            ['http_errors' => false]
        );
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($status !== 200) {
            $this->warn($uitId . ' - did not find user with UiTiD ' . $uitId);
            return null;
        }

        $xmlString = Str::of($body);

        $total = $xmlString->between('<total>', '</total>')->toInteger();
        if ($total === 0) {
            $this->warn($uitId . ' - did not find user with UiTiD ' . $uitId);
            return null;
        }

        if (!$xmlString->contains('<foaf:mbox>')) {
            $this->warn($uitId . ' - has no mbox inside UiTiD');
            return null;
        }

        $email = $xmlString->between('<foaf:mbox>', '</foaf:mbox>')->toString();

        return new Contact(
            Uuid::fromString($uitId),
            Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            $email,
            ContactType::Contributor,
            'first',
            'Last'
        );
    }

    private function getContactFromInInsightly(string $uitId, int $insightlyId): ?Contact
    {
        try {
            $contactAsArray = $this->insightlyClient->contacts()->get($insightlyId);
        } catch (RecordNotFound) {
            $this->warn($uitId . ' - contact with insightly id ' . $insightlyId . ' not found inside Insightly');
            return null;
        }

        return new Contact(
            Uuid::fromString($uitId),
            Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            $contactAsArray['EMAIL_ADDRESS'],
            ContactType::Contributor,
            $contactAsArray['FIRST_NAME'],
            $contactAsArray['LAST_NAME'] ?? ''
        );
    }
}
