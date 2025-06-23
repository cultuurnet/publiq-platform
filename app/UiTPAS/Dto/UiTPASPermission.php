<?php

declare(strict_types=1);

namespace App\UiTPAS\Dto;

use App\Domain\Udb3Uuid;

final readonly class UiTPASPermission
{
    public function __construct(public Udb3Uuid $organizerId, public string $organizerName, public UiTPASPermissionDetails $permissionDetails)
    {

    }

    public static function fromArray(array $data): self
    {
        return new self(
            new Udb3Uuid($data['organizer']['id']),
            $data['organizer']['name'],
            new UiTPASPermissionDetails(array_map(
                static fn (array $detail) => UiTPASPermissionDetail::fromArray($detail),
                $data['permissionDetails'] ?? []
            ))
        );
    }
}
