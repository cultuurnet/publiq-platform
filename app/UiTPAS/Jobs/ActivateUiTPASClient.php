<?php

declare(strict_types=1);

namespace App\UiTPAS\Jobs;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final readonly class ActivateUiTPASClient
{
    use Dispatchable;

    public function __construct(public UuidInterface $id)
    {
    }
}
