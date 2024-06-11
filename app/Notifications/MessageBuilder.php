<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Domain\Integrations\Integration;

interface MessageBuilder
{
    public function toMessage(Integration $integration): string;
}
