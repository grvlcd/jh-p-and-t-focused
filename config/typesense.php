<?php

return [
    'host' => env('TYPESENSE_HOST'),
    'port' => (int) env('TYPESENSE_PORT'),
    'protocol' => env('TYPESENSE_PROTOCOL'),

    'admin_api_key' => env('TYPESENSE_ADMIN_API_KEY'),
    'search_only_api_key' => env('TYPESENSE_SEARCH_ONLY_API_KEY'),

    'collections' => [
        'protocols' => 'protocols',
        'threads' => 'threads',
    ],
];

