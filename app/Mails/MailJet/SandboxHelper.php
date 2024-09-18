<?php

declare(strict_types=1);

namespace App\Mails\MailJet;

use App\Domain\Mail\Addresses;

final readonly class SandboxHelper
{
    public function __construct(private bool $sandboxMode, private array $allowedDomains)
    {
    }

    public function getSandboxMode(Addresses $to): bool
    {
        if (!$this->sandboxMode) {
            return false;
        }

        foreach ($to as $address) {
            foreach ($this->allowedDomains as $domain) {
                if (str_ends_with($address->getAddress(), $domain)) {
                    return false;
                }
            }
        }

        return true;
    }
}
