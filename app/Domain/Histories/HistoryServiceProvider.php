<?php

declare(strict_types=1);

namespace App\Domain\Histories;

use App\Domain\Histories\Listeners\CreateHistory;
use App\Domain\Histories\Repositories\EloquentHistoryRepository;
use App\Domain\Histories\Repositories\HistoryRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class HistoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HistoryRepository::class, EloquentHistoryRepository::class);

        Event::listen(
            'App\Domain\*', [CreateHistory::class, 'handle']
        );
    }

    public function boot(): void
    {
    }
}
