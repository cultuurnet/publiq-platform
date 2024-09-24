<?php

declare(strict_types=1);

namespace Tests\Domain\KeyVisibilityUpgrades\Repositories;

use App\Domain\Integrations\KeyVisibility;
use App\Domain\KeyVisibilityUpgrades\Repositories\EloquentKeyVisibilityUpgradeRepository;
use App\Domain\KeyVisibilityUpgrades\KeyVisibilityUpgrade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentKeyVisibilityUpgradeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentKeyVisibilityUpgradeRepository $keyVisibilityUpgradeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->keyVisibilityUpgradeRepository = new EloquentKeyVisibilityUpgradeRepository();
    }

    public function test_it_can_create_a_key_visibility_upgrade(): void
    {
        $upgradeRequest = new KeyVisibilityUpgrade(
            Uuid::uuid4(),
            Uuid::uuid4(),
            KeyVisibility::v2
        );
        $this->keyVisibilityUpgradeRepository->save($upgradeRequest);

        $this->assertDatabaseHas('key_visibility_upgrades', [
            'id' => $upgradeRequest->id->toString(),
            'integration_id' => $upgradeRequest->integrationId->toString(),
            'key_visibility' => $upgradeRequest->keyVisibility->value,
        ]);
    }
}
