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
            'event.*',
            fn (string $eventName, array $data) => $this->app->get(CreateHistory::class)->handle($eventName, $data)
        );
    }

    public function boot(): void
    {
    }
}
