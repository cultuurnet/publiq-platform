<?php

declare(strict_types=1);

namespace App\UiTiDv1;

final class CachedUiTiDv1Status
{
    private array $statuses = [];

    public function __construct(private readonly UiTiDv1ClusterSDK $sdk)
    {
    }

    public function findStatusOnConsumer(UiTiDv1Consumer $consumer): UiTiDv1ConsumerStatus
    {
        if (! isset($this->statuses[$consumer->consumerKey])) {
            $this->statuses[$consumer->consumerKey] = $this->sdk->fetchStatusOfConsumer($consumer);
        }

        return $this->statuses[$consumer->consumerKey];
    }
}
