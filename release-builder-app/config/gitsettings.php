<?php

return [
    'committer' => [
        'name' => env('COMMITTER_NAME', 'Release Builder'),
        'email' => env('COMMITTER_EMAIL', 'no-reply@example.com'),
    ],
    'branch' => [
        'pattern' => env('RELEASE_BRANCH_PATTERN', 'release_{NAME}_{DATE}_{TIME}_{ID}'),
    ]
];
