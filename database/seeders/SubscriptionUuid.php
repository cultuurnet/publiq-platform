<?php

declare(strict_types=1);

namespace Database\Seeders;

enum SubscriptionUuid: string
{
    case BASIC_SEARCH_API_PLAN = 'b46745a1-feb5-45fd-8fa9-8e3ef25aac26';
    case BASIC_WIDGETS_PLAN = 'c470ccbf-074c-4bf1-b526-47c94c5e9296';
    case CUSTOM_SEARCH_API_PLAN = 'db9672ec-32e7-4d85-ba66-e8c2a6f5b9ca';
    case CUSTOM_WIDGETS_PLAN = '9bb5ad2a-0d11-4f50-8602-f84ed27c7e92';
    case FREE_ENTRY_API_PLAN = '6311ba66-91c2-4905-a182-150f1cdf4825';
    case PLUS_WIDGETS_PLAN = 'df21535b-ebc2-42dd-bf4c-2e50caf8af02';
    case FREE_UITPAS_API_PLAN = '52bb667f-d4da-47cd-9a76-f8896be410bd';
    case UITNETWERK_SEARCH_API_PLAN = '434f91fe-5eb6-4627-a39a-a31f49d96a71';
    case UITNETWERK_WIDGETS_PLAN = 'c98b910f-8784-4410-b1f8-fc6ba4c79ede';
}
