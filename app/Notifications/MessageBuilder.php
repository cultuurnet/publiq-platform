<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\UdbOrganizer;

interface MessageBuilder
{
    public function toMessage(Integration $integration): string;
    public function toMessageWithOrganizer(Integration $integration, UdbOrganizer $org): string;
}
