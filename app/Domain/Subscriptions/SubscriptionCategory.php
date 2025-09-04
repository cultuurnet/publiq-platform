<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions;

enum SubscriptionCategory: string
{
    case Free = 'Free';
    case Basic = 'Basic';
    case Plus = 'Plus';
    case Custom = 'Custom';
    case Uitnetwerk = 'Uitnetwerk';
}
