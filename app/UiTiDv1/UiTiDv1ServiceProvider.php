<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use App\UiTiDv1\Repositories\EloquentUiTiDv1ConsumerRepository;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use Illuminate\Support\ServiceProvider;

final class UiTiDv1ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UiTiDv1ConsumerRepository::class, EloquentUiTiDv1ConsumerRepository::class);
    }
}
