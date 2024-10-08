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

    public function updateConsumersForIntegration(Integration $integration, UiTiDv1Consumer ...$uiTiDv1Consumers): void
    {
        foreach ($uiTiDv1Consumers as $uiTiDv1Consumer) {
            $this->uitidv1EnvironmentSDKs[$uiTiDv1Consumer->environment->value]->updateConsumerForIntegration(
                $integration,
                $uiTiDv1Consumer
            );
        }
    }

    /**
     * @throws UiTiDv1EnvironmentNotConfigured
     */
    public function createConsumerForIntegrationOnEnvironment(
        Integration $integration,
        UiTiDv1Environment $environment
    ): UiTiDv1Consumer {
        if (!array_key_exists($environment->value, $this->uitidv1EnvironmentSDKs)) {
            throw new UiTiDv1EnvironmentNotConfigured($environment);
        }

        return $this->uitidv1EnvironmentSDKs[$environment->value]->createConsumerForIntegration($integration);
    }

    public function blockConsumers(Integration $integration, UiTiDv1Consumer ...$uiTiDv1Consumers): void
    {
        foreach ($uiTiDv1Consumers as $uiTiDv1Consumer) {
            $this->uitidv1EnvironmentSDKs[$uiTiDv1Consumer->environment->value]->blockConsumer($integration, $uiTiDv1Consumer);
        }
    }

    public function unblockConsumers(Integration $integration, UiTiDv1Consumer ...$uiTiDv1Consumers): void
    {
        foreach ($uiTiDv1Consumers as $uiTiDv1Consumer) {
            $this->uitidv1EnvironmentSDKs[$uiTiDv1Consumer->environment->value]->unblockConsumer($integration, $uiTiDv1Consumer);
        }
    }

    public function fetchStatusOfConsumer(UiTiDv1Consumer $uiTiDv1Consumer): UiTiDv1ConsumerStatus
    {
        return $this->uitidv1EnvironmentSDKs[$uiTiDv1Consumer->environment->value]->fetchStatusOfConsumer($uiTiDv1Consumer);
    }
}
