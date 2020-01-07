<?php

$router->post(config('sms.router.webhookEndpoint'), ['uses' => 'SmsController@webhook', 'as' => config('sms.router.namedPrefix').'.webhook']);

