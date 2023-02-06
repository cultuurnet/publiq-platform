<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Migrations\MigrateCoupons;
use App\Console\Migrations\MigrateProjects;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    protected $commands = [
        MigrateCoupons::class,
        MigrateProjects::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        $this->load(__DIR__ . '/Commands/Migrations');

        require base_path('routes/console.php');
    }
}
