<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum IntegrationStatus: string
{
    case Draft = 'draft';
    case Active  = 'active';
    case Blocked = 'blocked';
    case Deleted = 'deleted';
    case PendingApprovalIntegration = 'pending_approval_integration';
    case PendingApprovalPayment = 'pending_approval_payment';
}
