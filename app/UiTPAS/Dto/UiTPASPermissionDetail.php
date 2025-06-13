<?php

declare(strict_types=1);

namespace App\UiTPAS\Dto;

final readonly class UiTPASPermissionDetail
{
    public function __construct(public string $id, public string $label)
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['label']['nl'] ?? ''
        );
    }
}
