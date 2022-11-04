<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use Laravel\Nova\Card;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;

final class Main extends Dashboard
{
    /**
     * @return array<Card>
     */
    public function cards(): array
    {
        return [
            new Help(),
        ];
    }
}
