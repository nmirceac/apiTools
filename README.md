# apiTools
API Tools

## Contents
1. Intro
2. Examples

# 1. Intro

## How to install?

- composer require nmirceac/api-tools
- php artisan vendor:publish
- check config/api.php (just in case)
- add your API details to .env
- php artisan apitools:docs - to generate the documentation
- check the examples below
- enjoy! 

## Samples

### .env sample config


# 2. Examples

## Api controller

``` php
<?php

namespace App\Http\Controllers\Api;

/**
 * Class ApiController
 * @apiDescription Exposing the API interfaces
 * @package App\Http\Controllers\Api
 */
class Api extends \ApiTools\Http\Controllers\ApiController
{
    protected $intro = [
        ['type'=>'h1', 'content'=>'Collaboration Admin interface'],

        ['type'=>'h2', 'content'=>'This is an interface that gives access to the data structures required by this project'],
        ['type'=>'paragraph', 'content'=>'It can be easily used with the api-interface and api-client packages'],
        ['type'=>'h2', 'content'=>'Clients, Projects, Posts and Settings are a few of the important data structures'],
        ['type'=>'paragraph', 'content'=>'All packed in nice JSON structures'],
    ];

    protected $exposed = [
        Client::class,
        Content::class,
        Project::class,
        Setting::class,
        User::class,
    ];
}

```

## User controller

``` php
<?php

namespace App\Http\Controllers\Api;

/**
 * Class User
 * @apiModel User
 * @apiDescription Exposing the Users API interfaces
 * @package App\Http\Controllers\Api
 */
class User extends \ApiTools\Http\Controllers\BaseController
{
    public $class = \App\User::class;
    protected $itemName = 'client';
    protected $orderAsc = false;

    protected $orderBy = 'created_at';
    protected $itemsPerPage = 15;

    protected $singleAppends = ['thumbnail'];
    protected $multipleAppends = ['thumbnail'];

    protected $singleRelationships = ['images'];
    protected $multipleRelationships = [];

    protected $searchColumns = [];

}

```

## API routes
``` php
...
Route::get('/users', ['uses' => 'Api\User@index']);
Route::get('/users/{id}', ['uses' => 'Api\User@get']);
...
```

