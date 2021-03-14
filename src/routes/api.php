<?php

$router->get(config('api.router.schemaEndpoint'), ['uses' => 'Api\Api@schema', 'as'=>config('api.router.namedPrefix').'.schema']);
$router->get('dotNetPublishCode', ['uses' => 'Api\Api@showCode', 'as'=>config('api.router.namedPrefix').'.dotNetPublishShowCode']);


