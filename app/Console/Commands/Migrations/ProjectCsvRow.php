<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;

final class ProjectCsvRow
{
    private array $projectAsArray;

    public function __construct(array $projectAsArray)
    {
        $this->projectAsArray = array_map(
            fn (string $value) => $value !== 'NULL' ? $value : null,
            $projectAsArray
        );
    }

    public function id(): string
    {
        return $this->projectAsArray[0];
    }

    public function name(): string
    {
        return $this->projectAsArray[3];
    }

    public function description(): ?string
    {
        return $this->projectAsArray[16];
    }

    public function status(): IntegrationStatus
    {
        return match ($this->projectAsArray[7]) {
            'active' => IntegrationStatus::Active,
            'blocked' => IntegrationStatus::Blocked,
            'application_sent' => IntegrationStatus::PendingApprovalIntegration,
            'waiting_for_payment' => IntegrationStatus::PendingApprovalPayment,
            default => IntegrationStatus::Draft,
        };
    }

    public function type(): IntegrationType
    {
        return match ($this->projectAsArray[6]) {
            (string) config('insightly.integration_types.widgets') => IntegrationType::Widgets,
            (string) config('insightly.integration_types.search_api') => IntegrationType::SearchApi,
            default => IntegrationType::EntryApi,
        };
    }

    public function coupon(): ?string
    {
        if ($this->projectAsArray[8] === 'import') {
            return null;
        }

        return $this->projectAsArray[8];
    }

    public function userUiTiD(): string
    {
        return $this->projectAsArray[1];
    }

    public function insightlyOpportunityId(): ?int
    {
        return $this->insightlyId(15);
    }

    public function insightlyProjectId(): ?int
    {
        return $this->insightlyId(14);
    }

    private function insightlyId(int $index): ?int
    {
        if ($this->projectAsArray[$index] === null) {
            return null;
        }

        return (int) $this->projectAsArray[$index];
    }

    public function apiKeyTest(): ?string
    {
        return $this->projectAsArray[13];
    }

    public function apiKeyProduction(): ?string
    {
        return $this->projectAsArray[12];
    }

    public function subscriptionCategory(): string
    {
        if ($this->type() === IntegrationType::EntryApi) {
            return 'Free';
        }

        return $this->projectAsArray[18] ?? 'Basic';
    }
}
