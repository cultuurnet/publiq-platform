<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Listeners;

use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\KeyVisibilityUpgrades\Events\KeyVisibilityUpgradeCreated;
use App\Domain\KeyVisibilityUpgrades\Repositories\KeyVisibilityUpgradeRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class UpgradeKeyVisibility implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeyVisibilityUpgradeRepository $keyVisibilityUpgradeRepository
    ) {
    }

    public function handle(KeyVisibilityUpgradeCreated $keyVisibilityUpgradeCreated): void
    {
        $keyVisibilityUpgrade = $this->keyVisibilityUpgradeRepository->getById($keyVisibilityUpgradeCreated->id);

        $integration = $this->integrationRepository->getById($keyVisibilityUpgrade->integrationId);

        // For now there is only an upgrade possible from v1 to v2, so all can be used.
        $this->integrationRepository->update($integration->withKeyVisibility(KeyVisibility::all));
    }
}
