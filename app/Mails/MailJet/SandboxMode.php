<?php

declare(strict_types=1);

namespace App\Mails\MailJet;

use Symfony\Component\Mime\Address;

final readonly class SandboxMode
{
    public function __construct(private bool $sandboxMode, private array $allowedDomains)
    {
    }

    public function forAddress(Address $address): bool
    {
        if (!$this->sandboxMode) {
            return false;
        }

        foreach ($this->allowedDomains as $domain) {
            if (str_ends_with($address->getAddress(), $domain)) {
                return false;
            }
        }

        return true;
    }
}
