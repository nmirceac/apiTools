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
- add the 'api-tools' middleware in your \App\Http\Kernel's api section 
- check the examples below
- enjoy! 

## Kernel config sample

```` php

/**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'api-tools', // add here
            'throttle:3600,1', // you might also want to adjust the throttling
            'bindings',
        ],
    ];

````

## Samples

### .env sample config

``` dotenv

API_SECRET="23ur32pruERGRE32pojr32porj32porj32f23"
API_DEBUG=true
API_DOCS_PUBLIC=false
API_DOCS_THEME=true

```

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

## Methods examples

``` php
<?php

    // sendReponse
    //
    // for get method, the response should be wrapped in a sendResponse

    /**
     * @apiDescription Get age bracket from age
     * @param string $age
     * @apiExampleParamAge 33
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgeBracketFromAge($age)
    {
        return $this->sendResponse(\App\Calculator::getAgeBracketFromAge($age));
    }


    // apiExampleReturn
    //
    // docblock example response

    /**
     * @apiDescription Get government departments
     * @return \Illuminate\Http\JsonResponse
     * @apiExampleReturn {"success":true,"data":[{"id":1,"label":"Agriculture, Forestry and Fisheries [ Department of ]"},{"id":2,"label":"Arts and Culture [ Department of ]"},...{"id":49,"label":"Not sure"}]}
     */
    public function getGovernmentDepartments()
    {
        $options = [];
        foreach(\App\GovernmentDepartments::pluck('department_name', 'id') as $id=>$label) {
            $options[] = ['id'=>$id, 'label'=>$label];
        }

        return $this->sendResponse($options);
    }

    // apiExampleParamName
    //
    // docblock example parameters for documentation

    /**
     * @apiDescription BMI Calculator
     * @param int $height
     * @param int $weight
     * @apiExampleParamHeight 179
     * @apiExampleParamWeight 85
     * @apiExampleReturn {"success":true,"data":{"bmi":26.53,"description":"Overweight"}}
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBmi(int $height, int $weight)
    {
        return $this->sendResponse(\App\Calculator::calculateBmi($height, $weight));
    }



    // sendAck
    //
    // post routes expect a sendAck response - its content is optional

    /**
     * @apiDescription MyCover calculator
     * @apiRequestParamLife_code 34-M-NS-3
     * @apiRequestParamLpp 2
     * @apiRequestParamBenefactor 1
     * @apiRequestParamBeneficiary 3
     * @apiRequestParamMinimum_cover 1000000
     * $apiExampleReturn {"success":true,"data":{"premium":216,"maxBenefit":1000000,"prinicpleLiquidity":100000,"estateProtection":5000,"spouseLiquidity":30000,"childrenLiquidity":0,"childLiquidity":40000,"totalBenefits":1175000}}
     * @return \Illuminate\Http\JsonResponse
     */
    public function myCoverCalculator()
    {
        $lifeCode = request('life_code');
        $lpp = request('lpp');
        $benefactor = request('benefactor');
        $beneficiary = request('beneficiary');
        $minimumCover = request('minimum_cover');

        $summary = \App\Calculator::getMyCoverCalculations($lifeCode, $lpp, $benefactor, $beneficiary, $minimumCover);

        return $this->sendAck($summary);
    }


    // sendError
    //
    // this will throw the desired exception to the apiClient

    /**
     * @apiDescription Get participants for a user
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParticipants(int $id)
    {
        $i = $this->class::find($id);
        if(is_null($i)) {
            return $this->sendError('User not found', [], 404);
        }

        return $this->sendResponse($i->participants()->with('submissions')->get());
    }


    // apiSupportsPagination
    //
    // auto pagination example - @apiSupportsPagination
    // the apiClient published method will automaticall add the current page
    //
    // apiRequestParamPage
    // this will add a request parameter "page" with the value of "1"
    // in the documentation example  

    /**
     * @apiDescription Returns upgrades for intermediary
     * @param int $id
     * @apiExampleId 3
     * @apiSupportsPagination
     * @apiRequestParamPage 1
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForIntermediary(int $id)
    {
        $with = [];
        if(request('with')) {
            $with = explode(',', request('with', ''));
        }

        $upgrades = $this->class::query()
            ->with($with)
            ->paginate();

        return self::sendResponse($upgrades);
    }


    // apiPostParamPassword auto add post params to published method
    //
    // the generated api client method will have the following parameters
    // setPassword(int $id, $password, $data=[]);

    /**
     * @apiDescription Set user's password
     * @param int $id
     * @apiRequestParamPassword testPassword
     * @apiPostParamPassword password
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPassword(int $id)
    {
        $i = $this->class::with($this->singleRelationships);
        $i = $i->find($id);

        if (!$i) {
            return $this->sendError('Not Found');
        }

        $i->password = request('password');
        $i->save();

        return $this->sendAck();
    }

?>
```

## API routes
``` php
...
Route::get('/users', ['uses' => 'Api\User@index']);
Route::get('/users/{id}', ['uses' => 'Api\User@get']);


Route::group(['prefix'=>'calculators'], function () {
    Route::get('/getBmi/{height}/{weight}', ['uses' => 'Api\Calculator@getBmi']);
    Route::get('/getRatingCategory/{education}/{income}', ['uses' => 'Api\Calculator@getRatingCategory']);
    Route::get('/getLifeCode/{age}/{gender}/{smoker}/{ratingCategory}', ['uses' => 'Api\Calculator@getLifeCode']);
    Route::post('/keyplanCalculator', ['uses' => 'Api\Calculator@keyplanCalculator']);
    Route::post('/affordabilityChecker', ['uses' => 'Api\Calculator@affordabilityChecker']);
    Route::post('/myCoverCalculator', ['uses' => 'Api\Calculator@myCoverCalculator']);
});

...
```

