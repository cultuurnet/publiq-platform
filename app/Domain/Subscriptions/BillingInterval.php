<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions;

enum BillingInterval: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
}
