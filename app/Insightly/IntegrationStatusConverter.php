<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Integrations\IntegrationStatus;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;

final class IntegrationStatusConverter
{
    public static function getOpportunitySate(IntegrationStatus $status): ?OpportunityState
    {
        return match ($status) {
            IntegrationStatus::Draft, IntegrationStatus::PendingApprovalIntegration, IntegrationStatus::PendingApprovalPayment => OpportunityState::OPEN,
            IntegrationStatus::Active => OpportunityState::WON,
            IntegrationStatus::Blocked => OpportunityState::SUSPENDED,
            default => null,
        };
    }

    public static function getProjectStage(IntegrationStatus $status): ?ProjectStage
    {
        return match ($status) {
            IntegrationStatus::Active => ProjectStage::LIVE,
            default => null,
        };
    }

    public static function getProjectState(IntegrationStatus $status): ?ProjectState
    {
        return match ($status) {
            IntegrationStatus::Active => ProjectState::COMPLETED,
            IntegrationStatus::Blocked => ProjectState::CANCELLED,
            default => null,
        };
    }
}
