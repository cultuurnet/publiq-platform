<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions;

enum SubscriptionUuid: string
{
    case BASIC_SEARCH_API_PLAN = 'b46745a1-feb5-45fd-8fa9-8e3ef25aac26';
    case BASIC_WIDGETS_PLAN = 'c470ccbf-074c-4bf1-b526-47c94c5e9296';
    case CUSTOM_SEARCH_API_PLAN = 'db9672ec-32e7-4d85-ba66-e8c2a6f5b9ca';
    case CUSTOM_WIDGETS_PLAN = '9bb5ad2a-0d11-4f50-8602-f84ed27c7e92';
    case FREE_ENTRY_API_PLAN = '6311ba66-91c2-4905-a182-150f1cdf4825';
    case PLUS_WIDGETS_PLAN = 'df21535b-ebc2-42dd-bf4c-2e50caf8af02';
}
