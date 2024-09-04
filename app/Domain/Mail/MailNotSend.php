<?php

declare(strict_types=1);

namespace App\Domain\Mail;

final class MailNotSend extends \DomainException
{
    public function __construct(?string $reason = null, ?array $variables = null)
    {
        $reason = $reason ?? 'Unknown reason';
        $message = $variables === null
            ? sprintf('Failed to send mail: %s', $reason)
            : sprintf('Failed to send mail: %s with params: %s', $reason, json_encode($variables, JSON_THROW_ON_ERROR));

        parent::__construct($message);
    }
}
