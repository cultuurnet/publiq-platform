<?php

declare(strict_types=1);

namespace Tests\UiTiDv1;

use App\UiTiDv1\UiTiDv1ClusterSDK;
use App\UiTiDv1\UiTiDv1Environment;
use App\UiTiDv1\UiTiDv1EnvironmentSDK;
use GuzzleHttp\ClientInterface;

trait CreatesMockUiTiDv1ClusterSDK
{
    public function createMockUiTiDv1ClusterSDK(ClientInterface $httpClient): UiTiDv1ClusterSDK
    {
        return new UiTiDv1ClusterSDK(
            new UiTiDv1EnvironmentSDK(
                UiTiDv1Environment::Acceptance,
                $httpClient,
                [
                    'entry-api' => [1,2],
                    'search-api' => [3],
                    'widgets' => [4,5,6],
                ]
            ),
            new UiTiDv1EnvironmentSDK(
                UiTiDv1Environment::Testing,
                $httpClient,
                [
                    'entry-api' => [7,8],
                    'search-api' => [9],
                    'widgets' => [10,11,12],
                ]
            ),
            new UiTiDv1EnvironmentSDK(
                UiTiDv1Environment::Production,
                $httpClient,
                [
                    'entry-api' => [13,14],
                    'search-api' => [15],
                    'widgets' => [16,17,18],
                ]
            ),
        );
    }
}
