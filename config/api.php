<?php

return [
    'router'=> [
        'includeRoutes'=>true,
        'prefix'=>'api',
        'namedPrefix'=>'api-tools',
        'schemaEndpoint'=>'schema',
        'middleware' => [
            'api', 'api-tools'
        ],
    ],

    'debug'=>env('API_DEBUG', false),
    'secret'=>env('API_SECRET', false),

    'docs'=> [
        'includeTheme' => env('API_DOCS_THEME', true),
    ],

    'api' => [
        'currentVersion'=>'master',
    ],
];

