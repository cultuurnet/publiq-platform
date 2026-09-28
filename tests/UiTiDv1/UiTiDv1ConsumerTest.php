<?php

declare(strict_types=1);

namespace Tests\UiTiDv1;

use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UiTiDv1ConsumerTest extends TestCase
{
    public function testItDoesNotSerializeTheConsumerCredentials(): void
    {
        $id = Uuid::uuid4();
        $integrationId = Uuid::uuid4();

        $consumer = new UiTiDv1Consumer(
            $id,
            $integrationId,
            'consumer-id',
            'consumer-key',
            'consumer-secret',
            'api-key',
            UiTiDv1Environment::Production
        );

        $this->assertSame(
            [
                'id' => $id->toString(),
                'integrationId' => $integrationId->toString(),
                'apiKey' => 'api-key',
                'environment' => UiTiDv1Environment::Production->value,
            ],
            json_decode(json_encode($consumer, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR)
        );
    }
}
