<?php

declare(strict_types=1);

namespace App\Auth0\Jobs;

use App\Auth0\Events\BlockClient;
use App\Auth0\Events\ClientBlocked;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class BlockClientListener implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(BlockClient $blockClient): void
    {
        ClientBlocked::dispatch($blockClient->id);
    }
}
