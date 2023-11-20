<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class Widget extends Controller
{
    public function handle(string $platformId, string $widgetId): JsonResponse
    {
        return new JsonResponse([
            'platformId' => $platformId,
            'widgetId' => $widgetId,
        ]);
    }
}
