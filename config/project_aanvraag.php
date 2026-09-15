<?php

declare(strict_types=1);

return [
    'create_widget' => env('PROJECT_AANVRAAG_CREATE_WIDGET', false),
    'base_uri' => env('PROJECT_AANVRAAG_BASE_URI', 'http://localhost/'),
    'timeout' => env('PROJECT_AANVRAAG_TIMEOUT', 10.0),

    // The group id widget-beheer expects for widget projects. This used to be derived from the last entry of
    // uitidv1.environments.prod.groups.widgets; it lives here now so the UiTiD v1 config can be removed without
    // silently changing the value that gets synced.
    'widget_group_id' => env('PROJECT_AANVRAAG_WIDGET_GROUP_ID'),
];
