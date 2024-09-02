<?php

declare(strict_types=1);

namespace App\Console\Commands\UiTPAS;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\Website;

final class UiTPASIntegration
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
}
