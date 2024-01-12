<?php

namespace App\Domain\Integrations\Listeners;

use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Events\IntegrationActivatedWithOrganization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DistributeKeys implements ShouldQueue
{
    use Queueable;


    public function handle(IntegrationActivatedWithCoupon|IntegrationActivatedWithOrganization $integrationActivatedWithCoupon): void
    {

    }
}
