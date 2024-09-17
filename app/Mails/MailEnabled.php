<?php

declare(strict_types=1);

namespace App\Mails;

use Ramsey\Uuid\UuidInterface;

interface MailEnabled
{
    public function getId(): UuidInterface;
}
