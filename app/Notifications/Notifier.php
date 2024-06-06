<?php

declare(strict_types=1);

namespace App\Notifications;

interface Notifier
{
    public function postMessage(string $message): void;
}
