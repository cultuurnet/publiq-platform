<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use App\Mails\Template\MailTemplate;
use Symfony\Component\Mime\Address;

interface Mailer
{
    public function send(Address $from, Address $to, MailTemplate $mailTemplate, array $variables = []): void;
}
