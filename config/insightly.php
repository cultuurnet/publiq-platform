<?php

declare(strict_types=1);

return [
    'enabled' => env('INSIGHTLY_INTEGRATION_ENABLED', false),

    'host' => env('INSIGHTLY_HOST', 'https://api.insight.ly/v3.1/'),

    'api_key' => env('INSIGHTLY_API_KEY'),

    'pipelines' => [
        'opportunities' => [
            'id' => env('INSIGHTLY_OPPORTUNITIES_PIPELINE_ID', 949820),
            'stages' => [
                'test' => env('INSIGHTLY_OPPORTUNITIES_PIPELINE_STAGE_TEST', 3894948),
                'request' => env('INSIGHTLY_OPPORTUNITIES_PIPELINE_STAGE_REQUEST', 3894949),
                'information' => env('INSIGHTLY_OPPORTUNITIES_PIPELINE_STAGE_INFORMATION', 3894950),
                'offer' => env('INSIGHTLY_OPPORTUNITIES_PIPELINE_STAGE_OFFER', 3894951),
                'closed' => env('INSIGHTLY_OPPORTUNITIES_PIPELINE_STAGE_CLOSED', 3894952),
            ],
        ],
        'projects' => [
            'id' => env('INSIGHTLY_PROJECTS_PIPELINE_ID', 949819),
            'stages' => [
                'test' => env('INSIGHTLY_PROJECTS_PIPELINE_STAGE_TEST', 3894944),
                'request' => env('INSIGHTLY_PROJECTS_PIPELINE_STAGE_START', 3894945),
                'information' => env('INSIGHTLY_PROJECTS_PIPELINE_STAGE_EVALUATION', 3894946),
                'offer' => env('INSIGHTLY_PROJECTS_PIPELINE_STAGE_LIVE', 3894947),
                'closed' => env('INSIGHTLY_PROJECTS_PIPELINE_STAGE_ENDED', 3936051),
            ],
        ],
    ],
];
