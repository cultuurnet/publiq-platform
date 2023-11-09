<?php

declare(strict_types=1);

return [
    'create_widget' => env('PROJECT_AANVRAAG_CREATE_WIDGET', false),
    'base_uri' => env('PROJECT_AANVRAAG_BASE_URI', 'http://localhst'),
    'beheer_widgets' => env('PROJECT_AANVRAAG_BEHEER_WIDGETS', 'http://localhost:4200/project/'),
];
