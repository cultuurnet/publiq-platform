<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Rules;

use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Models\IntegrationUrlModel;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

final readonly class IntegrationUrlUniqueness
{
    public function __construct(private mixed $integrationId, private mixed $environment)
    {
    }

    public function singleLoginUrl(): Unique
    {
        return $this->scoped('type')->where('type', IntegrationUrlType::Login->value);
    }

    public function distinctUrl(mixed $type): Unique
    {
        return $this->scoped('url')->where('type', $type);
    }

    private function scoped(string $column): Unique
    {
        return Rule::unique(IntegrationUrlModel::class, $column)
            ->where('integration_id', $this->integrationId)
            ->where('environment', $this->environment);
    }
}
