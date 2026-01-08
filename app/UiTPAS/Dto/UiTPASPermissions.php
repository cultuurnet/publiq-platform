<?php

declare(strict_types=1);

namespace App\UiTPAS\Dto;

use App\Json;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * @extends Collection<int, UiTPASPermission>
 */
final class UiTPASPermissions extends Collection
{
    public static function loadFromJson(string $json): self
    {
        // Filter out permissions items for which the organizer has no id or no name
        $permissions = [];
        foreach (Json::decodeAssociatively($json) as $permission) {
            if (!isset($permission['organizer']['id']) || !isset($permission['organizer']['name'])) {
                Log::error('Invalid organization permission entry: ' . Json::encode($permission));
                continue;
            }
            $permissions[] = $permission;
        }

        return new self(array_map(
            static fn (array $permission) => UiTPASPermission::fromArray($permission),
            $permissions
        ));
    }
}
