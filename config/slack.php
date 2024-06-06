<?php

declare(strict_types=1);

return [
    'botToken' => env('SLACK_BOT_TOKEN'),
    'baseUri' => env('SLACK_BASE_URI'),
    'channelID' => env('SLACK_CHANNEL_ID'),
    'channels' => [
        'publiq_platform' => env('SLACK_PUBLIQ_PLATFORM_CHANNEL_ID'),
        'technical_support' => env('SLACK_TECHNICAL_SUPPORT_CHANNEL_ID'),
    ],
];
