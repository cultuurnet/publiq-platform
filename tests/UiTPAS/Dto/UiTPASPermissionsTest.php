<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Dto;

use App\Domain\UdbUuid;
use App\Json;
use App\UiTPAS\Dto\UiTPASPermission;
use App\UiTPAS\Dto\UiTPASPermissionDetail;
use App\UiTPAS\Dto\UiTPASPermissions;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class UiTPASPermissionsTest extends TestCase
{
    public function test_it_loads_from_json(): void
    {
        $permissions = [
            [
                'organizer' => ['id' => 'f668a72f-a35a-4758-ac62-948f1302eae5', 'name' => 'publiq VZW'],
                'permissionDetails' => [
                    ['id' => 'TARIFFS_READ', 'label' => ['nl' => 'Tarieven opvragen']],
                    ['id' => 'PASSES_READ', 'label' => ['nl' => 'Basis UiTPAS informatie ophalen']],
                ],
            ],
        ];

        $uitpasPermissions = UiTPASPermissions::loadFromJson(Json::encode($permissions));
        $this->assertCount(1, $uitpasPermissions);

        /** @var UiTPASPermission $uitpasPermission */
        $uitpasPermission = $uitpasPermissions[0];
        $this->assertEquals(new UdbUuid('f668a72f-a35a-4758-ac62-948f1302eae5'), $uitpasPermission->organizerId);
        $this->assertEquals('publiq VZW', $uitpasPermission->organizerName);
        $this->assertCount(2, $uitpasPermission->permissionDetails);

        /** @var UiTPASPermissionDetail[] $permissionDetails */
        $permissionDetails = $uitpasPermission->permissionDetails;
        $this->assertEquals('TARIFFS_READ', $permissionDetails[0]->id);
        $this->assertEquals('Tarieven opvragen', $permissionDetails[0]->label);
        $this->assertEquals('PASSES_READ', $permissionDetails[1]->id);
        $this->assertEquals('Basis UiTPAS informatie ophalen', $permissionDetails[1]->label);
    }

    public function test_it_filters_incomplete_organizers(): void
    {
        Log::shouldReceive('error')
            ->times(2)
            ->withArgs(fn (string $message) => str_starts_with($message, 'Invalid organization permission entry: '));

        $permissions = [
            [
                'organizer' => ['id' => 'f668a72f-a35a-4758-ac62-948f1302eae5', 'name' => 'publiq VZW'],
                'permissionDetails' => [],
            ],
            [
                'organizer' => ['name' => 'Missing ID Organizer'],
                'permissionDetails' => [],
            ],
            [
                'organizer' => ['id' => 'd4e5f6a7-b8c9-0d1e-2f3a-4b5c6d7e8f90'],
                'permissionDetails' => [],
            ],
        ];

        $uitpasPermissions = UiTPASPermissions::loadFromJson(Json::encode($permissions));

        $this->assertCount(1, $uitpasPermissions);

        /** @var UiTPASPermission $uitpasPermission */
        $uitpasPermission = $uitpasPermissions[0];
        $this->assertEquals(new UdbUuid('f668a72f-a35a-4758-ac62-948f1302eae5'), $uitpasPermission->organizerId);
    }
}
