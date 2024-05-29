<?php

declare(strict_types=1);

namespace App\Nova\ActionGuards\UiTiDv1;

use App\Nova\ActionGuards\ActionGuard;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1ConsumerStatus;

final readonly class UnblockUiTiDv1ConsumerGuard implements ActionGuard
{
    public function __construct(
        private UiTiDv1ClusterSDK $sdk,
    ) {
    }

    public function canDo(object $resource): bool
    {
        if (!$resource instanceof UiTiDv1Consumer) {
            return false;
        }

        $status = $this->sdk->fetchStatusOfConsumer($resource);

        return $status !== UiTiDv1ConsumerStatus::Active && $status !== UiTiDv1ConsumerStatus::Unknown;
    }
}
