<?php

declare(strict_types=1);

return [
    'create_widget' => env('PROJECT_AANVRAAG_CREATE_WIDGET', false),
    'base_uri' => env('PROJECT_AANVRAAG_BASE_URI', 'http://localhost/'),
    'timeout' => env('PROJECT_AANVRAAG_TIMEOUT', 10.0),
    'widget_group_id' => env('PROJECT_AANVRAAG_WIDGET_GROUP_ID'),
];
