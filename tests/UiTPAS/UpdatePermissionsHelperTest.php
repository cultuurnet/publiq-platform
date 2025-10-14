<?php

declare(strict_types=1);

namespace Tests\UiTPAS;

use App\Domain\UdbUuid;
use App\UiTPAS\UpdatePermissionsHelper;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class UpdatePermissionsHelperTest extends TestCase
{
    private UpdatePermissionsHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new UpdatePermissionsHelper();
    }

    public function test_merge_adds_new_permission_set_when_organizer_not_present(): void
    {
        $organizerId = new UdbUuid(Uuid::uuid4()->toString());
        $emptyPermissions = [];

        $result = $this->helper->merge($emptyPermissions, $organizerId);

        $this->assertCount(1, $result);
        $this->assertEquals($organizerId->toString(), $result[0]['organizer']['id']);
        $this->assertGreaterThan(0, count($result[0]['permissionDetails']));
    }

    public function test_merge_adds_only_missing_permissions_when_organizer_exists(): void
    {
        $organizerId = new UdbUuid(Uuid::uuid4()->toString());
        $existingPermissions = [
            [
                'organizer' => ['id' => $organizerId->toString()],
                'permissionDetails' => [
                    ['id' => 'CHECKINS_WRITE'],
                    ['id' => 'EVENTS_READ'],
                ],
            ],
        ];
        $expectedPermissionCount = count(UpdatePermissionsHelper::PERMISSION_LIST);

        $result = $this->helper->merge($existingPermissions, $organizerId);

        $resultPermissionIds = array_column($result[0]['permissionDetails'], 'id');
        $this->assertContains('CHECKINS_WRITE', $resultPermissionIds);
        $this->assertContains('EVENTS_READ', $resultPermissionIds);
        $this->assertContains('REWARDS_PASSHOLDERS_READ', $resultPermissionIds);
        $this->assertCount($expectedPermissionCount, $resultPermissionIds);
    }

    public function test_merge_does_not_duplicate_permissions(): void
    {
        $organizerId = new UdbUuid(Uuid::uuid4()->toString());
        $allPermissionDetails = array_map(fn ($id) => ['id' => $id], UpdatePermissionsHelper::PERMISSION_LIST);
        $permissionsWithAllDetails = [
            [
                'organizer' => ['id' => $organizerId->toString()],
                'permissionDetails' => $allPermissionDetails,
            ],
        ];
        $expectedPermissionCount = 15;

        $result = $this->helper->merge($permissionsWithAllDetails, $organizerId);

        $resultPermissionIds = array_column($result[0]['permissionDetails'], 'id');
        $this->assertCount($expectedPermissionCount, $resultPermissionIds);
        $this->assertEqualsCanonicalizing(
            UpdatePermissionsHelper::PERMISSION_LIST,
            $resultPermissionIds
        );
    }
}
