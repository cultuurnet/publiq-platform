<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use Symfony\Component\Mime\Address;

interface Mailer
{
    public function send(Address $from, Address $to, int $templateId, array $variables = []): void;
}
