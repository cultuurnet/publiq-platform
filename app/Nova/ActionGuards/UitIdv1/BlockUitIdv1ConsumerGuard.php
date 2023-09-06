<?php

declare(strict_types=1);

namespace App\Nova\ActionGuards\UitIdv1;

use App\Nova\ActionGuards\ActionGuard;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1ConsumerStatus;

final readonly class BlockUitIdv1ConsumerGuard implements ActionGuard
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

        return $this->sdk->fetchStatusOfConsumer($resource) === UiTiDv1ConsumerStatus::Active;
    }

}
