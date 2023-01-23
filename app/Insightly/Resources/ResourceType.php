<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

enum ResourceType: string
{
    case Contact = 'contact';
    case Opportunity = 'opportunity';
    case Organization = 'organization';
    case Project = 'Project';
}
