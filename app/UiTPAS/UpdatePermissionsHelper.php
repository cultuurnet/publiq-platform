<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Domain\UdbUuid;

/***
 * Helper class to update permissions by merging new permissions with existing ones.
 */
final class UpdatePermissionsHelper
{
    public const PERMISSION_LIST = [
        'CHECKINS_WRITE',
        'EVENTS_READ',
        'EVENTS_UPDATE',
        'TICKETSALES_REGISTER',
        'TICKETSALES_SEARCH',
        'ORGANIZERS_SEARCH',
        'TARIFFS_READ',
        'MEMBERSHIP_PRICES_READ',
        'PASSES_READ',
        'PASSES_INSZNUMBERS_READ',
        'PASSES_CHIPNUMBERS_READ',
        'REWARDS_READ',
        'REWARDS_WRITE',
        'REWARDS_REDEEM',
        'REWARDS_PASSHOLDERS_READ',
    ];

    public function merge(array $permissions, UdbUuid $organizerId): array
    {
        $existingPermissionIndex = null;
        foreach ($permissions as $index => $permission) {
            if ($permission->organizer->id === $organizerId->toString()) {
                $existingPermissionIndex = $index;
                break;
            }
        }

        if ($existingPermissionIndex !== null) {
            $newPermissionDetails = $this->withBody($organizerId)->permissionDetails;
            $existingPermissionDetails = $permissions[$existingPermissionIndex]->permissionDetails ?? [];

            $existingIds = array_column($existingPermissionDetails, 'id');

            foreach ($newPermissionDetails as $newPermission) {
                if (!in_array($newPermission->id, $existingIds, true)) {
                    $permissions[$existingPermissionIndex]->permissionDetails[] = $newPermission;
                }
            }
        } else {
            $permissions[] = $this->withBody($organizerId);
        }
        return $permissions;
    }

    private function withBody(UdbUuid $organizerId): \stdClass
    {
        $body = new \stdClass();
        $body->organizer = new \stdClass();
        $body->organizer->id = $organizerId->toString();
        $body->permissionDetails = array_map(
            static fn (string $id): \stdClass => (object) ['id' => $id],
            self::PERMISSION_LIST
        );
        return $body;
    }
}
