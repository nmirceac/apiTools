<?php

return [
    'router'=> [
        'includeRoutes'=>true,
        'prefix'=>'api',
        'namedPrefix'=>'api-tools',
        'schemaEndpoint'=>'schema',
        'middleware' => [
            'auth', 'api', 'api-tools'
        ],
    ],

    'debug'=>env('API_DEBUG', false),
    'allowAuthenticatedRequests'=>env('API_ALLOW_AUTHENTICATED_REQUESTS', false),

    'ray'=>env('API_RAY', false),
    'ray_log_class'=>env('API_RAY_LOG_CLASS', false),

    'ray_ip_whitelist'=> (!env('API_RAY_IP_WHITELIST') ? [] : explode(',', env('API_RAY_IP_WHITELIST'))),
    'ray_ip_blacklist'=> (!env('API_RAY_IP_BLACKLIST') ? [] : explode(',', env('API_RAY_IP_BLACKLIST'))),

    'logging' => [
        'enabled' => env('API_LOGGING', false),
        'model' => env('API_LOGGING_MODEL', false),
    ],

    'secret'=>env('API_SECRET', false),

    'docs'=> [
        'includeTheme' => env('API_DOCS_THEME', true),
    ],

    'api' => [
        'currentVersion'=>'master',
    ],
];

