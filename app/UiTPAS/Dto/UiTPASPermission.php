<?php

declare(strict_types=1);

namespace App\UiTPAS\Dto;

final class UiTPASPermission
{
    public string $organizerId;
    public string $organizerName;

    public UiTPASPermissionDetails $permissionDetails;

    public function __construct(string $organizerId, string $organizerName, UiTPASPermissionDetails $permissionDetails)
    {
        $this->organizerId = $organizerId;
        $this->organizerName = $organizerName;
        $this->permissionDetails = $permissionDetails;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['organizer']['id'],
            $data['organizer']['name'],
            new UiTPASPermissionDetails(array_map(
                static fn (array $detail) => UiTPASPermissionDetail::fromArray($detail),
                $data['permissionDetails'] ?? []
            ))
        );
    }
}
