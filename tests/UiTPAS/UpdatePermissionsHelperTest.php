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
    private UdbUuid $organizerId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new UpdatePermissionsHelper();
        $this->organizerId = new UdbUuid(Uuid::uuid4()->toString());
    }

    public function test_merge_adds_new_permission_set_when_organizer_not_present(): void
    {
        $result = $this->helper->merge([], $this->organizerId);

        $this->assertCount(1, $result);
        $this->assertEquals($this->organizerId->toString(), $result[0]['organizer']['id']);
        $this->assertGreaterThan(0, count($result[0]['permissionDetails']));
    }

    public function test_merge_adds_only_missing_permissions_when_organizer_exists(): void
    {
        $result = $this->helper->merge([
            [
                'organizer' => ['id' => $this->organizerId->toString()],
                'permissionDetails' => [
                    ['id' => 'CHECKINS_WRITE'],
                    ['id' => 'EVENTS_READ'],
                ],
            ],
        ], $this->organizerId);

        $resultPermissionIds = array_column($result[0]['permissionDetails'], 'id');
        $this->assertContains('CHECKINS_WRITE', $resultPermissionIds);
        $this->assertContains('EVENTS_READ', $resultPermissionIds);
        $this->assertContains('REWARDS_PASSHOLDERS_READ', $resultPermissionIds);
        $this->assertCount(count(UpdatePermissionsHelper::PERMISSION_LIST), $resultPermissionIds);
    }

    public function test_merge_does_not_duplicate_permissions(): void
    {
        $allPermissionDetails = array_map(fn ($id) => ['id' => $id], UpdatePermissionsHelper::PERMISSION_LIST);
        $result = $this->helper->merge([
            [
                'organizer' => ['id' => $this->organizerId->toString()],
                'permissionDetails' => $allPermissionDetails,
            ],
        ], $this->organizerId);

        $resultPermissionIds = array_column($result[0]['permissionDetails'], 'id');
        $this->assertCount(count(UpdatePermissionsHelper::PERMISSION_LIST), $resultPermissionIds);
        $this->assertEqualsCanonicalizing(
            UpdatePermissionsHelper::PERMISSION_LIST,
            $resultPermissionIds
        );
    }
}
