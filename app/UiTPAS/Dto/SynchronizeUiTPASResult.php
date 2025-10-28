<?php

declare(strict_types=1);

namespace App\UiTPAS\Dto;

final readonly class SynchronizeUiTPASResult
{
    public function __construct(public bool $success, public array $failedOrganizerIds = [])
    {

    }
}
