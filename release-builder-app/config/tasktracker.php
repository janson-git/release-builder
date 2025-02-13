<?php

return [
    'default' => env('TASK_TRACKER'),

    'services' => [
        'mayven' => [
            'api_url' => env('MAYVEN_API_BASE_URL'),
            'auth' => env('MAYVEN_AUTH'),
        ],
    ],
];
