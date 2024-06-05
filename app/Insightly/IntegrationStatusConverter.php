<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Integrations\IntegrationStatus;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Objects\ProjectState;

final class IntegrationStatusConverter
{
    public static function getOpportunityState(IntegrationStatus $status): OpportunityState
    {
        return match ($status) {
            IntegrationStatus::Active => OpportunityState::WON,
            IntegrationStatus::Blocked => OpportunityState::SUSPENDED,
            default => OpportunityState::OPEN,
        };
    }

    public static function getProjectState(IntegrationStatus $status): ProjectState
    {
        return match ($status) {
            IntegrationStatus::Blocked => ProjectState::CANCELLED,
            default => ProjectState::COMPLETED,
        };
    }
}
