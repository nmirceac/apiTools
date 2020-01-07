<?php

return [
    'router'=> [
        'includeRoutes'=>true,
        'prefix'=>'apitools',
        'namedPrefix'=>'api-tools',
        'webhookEndpoint'=>'webhook'
    ],

    'api' => [
        'endpoint' => env('SMS_API_ENDPOINT', 'https://sms.weanswer.it/api/v1/sms'),
        'key' => env('SMS_API_KEY'),
        'secret' => env('SMS_API_SECRET'),
        'webhook' => env('SMS_API_WEBHOOK')
    ],

];

