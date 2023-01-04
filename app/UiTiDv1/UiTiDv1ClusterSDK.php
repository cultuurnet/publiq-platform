<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use App\Domain\Integrations\Integration;

final class UiTiDv1ClusterSDK
{
    /**
     * @var UiTiDv1EnvironmentSDK[]
     */
    private array $uitidv1EnvironmentSDKs = [];

    public function __construct(UiTiDv1EnvironmentSDK ...$uitidv1EnvironmentSDKs)
    {
        foreach ($uitidv1EnvironmentSDKs as $uitidv1EnvironmentSDK) {
            $this->uitidv1EnvironmentSDKs[$uitidv1EnvironmentSDK->environment->value] = $uitidv1EnvironmentSDK;
        }
    }

    /**
     * @return UiTiDv1Consumer[]
     */
    public function createConsumersForIntegration(Integration $integration): array
    {
        return array_values(
            array_map(
                static fn (UiTiDv1EnvironmentSDK $sdk) => $sdk->createConsumerForIntegration($integration),
                $this->uitidv1EnvironmentSDKs
            )
        );
    }
}
