<?php

declare(strict_types=1);

namespace App\Search\UiTPAS;

use Illuminate\Contracts\Cache\Repository as Cache;

final readonly class CachedUiTPASLabelProvider implements UiTPASLabelProvider
{
    public function __construct(
        private UiTPASLabelProvider $provider,
        private Cache $cache
    ) {
    }

    public function getLabels(): array
    {
        return $this->cache->remember('uitpas_labels', now()->addHours(24), fn () => $this->provider->getLabels());
    }
}
