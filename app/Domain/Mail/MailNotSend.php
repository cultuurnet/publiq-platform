<?php

declare(strict_types=1);

namespace App\Domain\Mail;

final class MailNotSend extends \DomainException
{
    public function __construct(string $reason = null)
    {
        parent::__construct(sprintf('Failed to sent mail: %s', $reason ?? 'Unknown reason'));
    }
}
