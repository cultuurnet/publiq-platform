<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use App\Mails\Addresses;
use Symfony\Component\Mime\Address;

interface Mailer
{
    public function send(Address $from, Addresses $to, int $templateId, string $subject, array $variables = []): void;
}
