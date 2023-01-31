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

final class MigrateUsers extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:users';

    protected $description = 'Migrate the users provided in the users.csv CSV file';

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

        $usersAsArray = $this->readCsvFile('users.csv');

        $usersCount = count($usersAsArray);
        if ($usersCount <= 0) {
            $this->warn('No users to import');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to import ' . $usersCount . ' users?')) {
            return 0;
        }

        foreach ($usersAsArray as $userAsArray) {
            if (!is_array($userAsArray)) {
                continue;
            }

            [$uitId] = $userAsArray;

            $email = $this->findUserEmailInUiTiD($uitId);
            if ($email === null) {
                continue;
            }

            $insightlyIds = $this->insightlyClient->contacts()->findIdsByEmail($email);
            if (count($insightlyIds) > 1) {
                $this->warn($uitId . ' - found multiple contacts with email ' . $email);
            }

            if (count($insightlyIds)) {
                $insightlyId = Arr::sort($insightlyIds)[0];
            } else {
                $this->warn($uitId . ' - user with email ' . $email . ' not found as contact inside Insightly ');
                continue;
            }

            $contact = $this->findUserInInsightly($uitId, (int) $insightlyId);

            if ($contact === null) {
                continue;
            }

            $this->info($uitId . ' - importing user with email ' . $email . ' and Insightly id ' . $insightlyId);
            $this->contactRepository->save($contact);

            $insightlyMapping = new InsightlyMapping(
                Uuid::fromString($uitId),
                $insightlyId,
                ResourceType::Contact
            );
            $this->insightlyMappingRepository->save($insightlyMapping);
        }

        return 0;
    }

    private function findUserEmailInUiTiD(string $uitId): ?string
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

        return Str::of($body)->between('<foaf:mbox>', '</foaf:mbox>')->toString();
    }

    private function findUserInInsightly(string $uitId, int $insightlyId): ?Contact
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
