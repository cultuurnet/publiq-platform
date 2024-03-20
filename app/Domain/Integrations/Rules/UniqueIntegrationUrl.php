<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class UniqueIntegrationUrl implements ValidationRule
{
    /**
     * @param array<array> $urls
     */
    public function __construct(private array $urls)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $index = (int)explode('.', $attribute)[1];
        $currentUrl = $this->urls[$index];
        $inUse = [];

        // compare urls with the same type and environment
        foreach ($this->urls as $key => $url) {
            $hash = $url['type'] . $url['environment'];
            $inUse[$hash] ??= $key;

            if ($key === $index) {
                continue;
            }
            if ($currentUrl['type'] !== $url['type']) {
                continue;
            }
            if ($currentUrl['environment'] !== $url['environment']) {
                continue;
            }
            if (trim(($currentUrl['url'] ?? '')) !== trim($url['url'] ?? '')) {
                continue;
            }

            if ($inUse[$hash] === $key) {
                $fail('validation.distinct')->translate();
            }
        }
    }
}
