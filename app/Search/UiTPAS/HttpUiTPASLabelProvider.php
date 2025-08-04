<?php

declare(strict_types=1);

namespace App\Search\UiTPAS;

use App\UiTPAS\UiTPASConfig;
use Illuminate\Support\Facades\Http;

final readonly class HttpUiTPASLabelProvider implements UiTPASLabelProvider
{
    public function getLabels(): array
    {
        $uri = config(UiTPASConfig::UDB_BASE_IO_URI->value) . 'uitpas/labels';

        $response = Http::timeout(5)
            ->acceptJson()
            ->get($uri);

        if (!$response->ok()) {
            throw new \RuntimeException("Failed to fetch UiTPAS labels: " . $response->status());
        }

        return array_map(
            static fn (string $value) => 'labels:' . $value,
            $response->json()
        );
    }
}
