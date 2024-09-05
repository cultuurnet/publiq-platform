<?php

declare(strict_types=1);

namespace App\Console\Commands\UiTPAS;

use App\Auth0\Auth0Tenant;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\Website;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UiTPASIntegrationCsvRow
{
    private array $integrationAsArray;

    public function __construct(array $projectAsArray)
    {
        $this->integrationAsArray = array_map(
            fn (string $value) => $value !== 'NULL' ? $value : null,
            $projectAsArray
        );
    }

    public function name(): string
    {
        return $this->integrationAsArray[0];
    }

    public function status(): IntegrationStatus
    {
        return match ($this->integrationAsArray[1]) {
            'active' => IntegrationStatus::Active,
            'blocked' => IntegrationStatus::Blocked,
            'application_sent' => IntegrationStatus::PendingApprovalIntegration,
            'waiting_for_payment' => IntegrationStatus::PendingApprovalPayment,
            default => IntegrationStatus::Draft,
        };
    }

    public function description(): string
    {
        return $this->integrationAsArray[4];
    }

    public function website(): Website
    {
        return new Website($this->integrationAsArray[5]);
    }

    public function clientIdForTenant(Auth0Tenant $auth0Tenant): string
    {
        return match ($auth0Tenant) {
            Auth0Tenant::Acceptance => throw new InvalidArgumentException('No client ID for acceptance tenant'),
            Auth0Tenant::Testing => $this->integrationAsArray[2],
            Auth0Tenant::Production => $this->integrationAsArray[3],
        };
    }

    /**
     * @return array<Contact>
     */
    public function contacts(UuidInterface $integrationId): array
    {
        $contacts = [];

        $contacts[] = new Contact(
            id: Uuid::uuid4(),
            integrationId: $integrationId,
            email: $this->integrationAsArray[6],
            type: ContactType::Functional,
            firstName: $this->integrationAsArray[7],
            lastName: $this->integrationAsArray[8]
        );

        $contacts[] = new Contact(
            id: Uuid::uuid4(),
            integrationId: $integrationId,
            email: $this->integrationAsArray[9],
            type: ContactType::Technical,
            firstName: $this->integrationAsArray[10],
            lastName: $this->integrationAsArray[11]
        );

        $contacts[] = new Contact(
            id: Uuid::uuid4(),
            integrationId: $integrationId,
            email: $this->integrationAsArray[12],
            type: ContactType::Contributor,
            firstName: $this->integrationAsArray[13],
            lastName: $this->integrationAsArray[14]
        );

        if (!empty($this->integrationAsArray[15])) {
            $contacts[] = new Contact(
                id: Uuid::uuid4(),
                integrationId: $integrationId,
                email: $this->integrationAsArray[15],
                type: ContactType::Contributor,
                firstName: $this->integrationAsArray[16],
                lastName: $this->integrationAsArray[17]
            );
        }

        return $contacts;
    }
}
