<?php

declare(strict_types=1);

namespace App\Nova\ActionGuards;

interface ActionGuardInterface
{
    public function canDo(object $resource): bool;
}
