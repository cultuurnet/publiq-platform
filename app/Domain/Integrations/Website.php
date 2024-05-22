<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

final readonly class Website
{
    public function __construct(public string $value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Invalid website URL');
        }
    }
}
