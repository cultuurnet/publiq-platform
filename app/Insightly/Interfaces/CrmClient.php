<?php

declare(strict_types=1);

namespace App\Insightly\Interfaces;

use App\Insightly\Resources\OrganizationResource;

interface CrmClient
{
    public function contacts(): ContactResource;

    public function opportunities(): OpportunityResource;

    public function organizations(): OrganizationResource;
}
