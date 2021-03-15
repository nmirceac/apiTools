<?php

$router->get(config('api.router.schemaEndpoint'), ['uses' => 'Api\Api@schema', 'as'=>config('api.router.namedPrefix').'.schema']);
$router->get('dotNetCode/{section?}', ['uses' => 'Api\Api@dotNetCode', 'as'=>config('api.router.namedPrefix').'.dotNetCode']);


