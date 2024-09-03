<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Exceptions;

use App\Domain\Integrations\Integration;
use App\Domain\Subscriptions\Subscription;
use Exception;

final class InconsistentIntegrationTypeException extends Exception
{
    public function __construct(Integration $integration, Subscription $subscription)
    {
        parent::__construct(
            "Inconsistency between the type '{$integration->type->value}' for integration and type '{$subscription->integrationType->value}' for the linked subscription."
        );
    }
}
