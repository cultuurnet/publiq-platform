<?php

declare(strict_types=1);

namespace Tests\UiTiDv1;

use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait CreatesMockUiTiDv1Consumer
{
    private function createConsumer(UuidInterface $id): UiTiDv1Consumer
    {
        return new UiTiDv1Consumer(
            $id,
            Uuid::uuid4(),
            'consumer-id-1',
            'consumer-key-1',
            'api-key-1',
            UiTiDv1Environment::Acceptance
        );
    }
}
