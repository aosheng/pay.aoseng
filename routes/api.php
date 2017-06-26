<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     //return '456';
//     return $request->user();
// });

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api\V1',
    'middleware' => [
        //'cors',
        //'api.throttle'
    ],
    // each route have a limit of 100 of 1 minutes
    //'limit' => 100, 'expires' => 1
], function ($api) {
    // Auth
    // login
    $api->get('/user', function(){
        return '123';
    });
    $api->post('Api500EasyPay', [
        'as' => 'Api500EasyPay.index',
        'uses' => 'Api500EasyPayController@index',
    ]);
    $api->post('Api500EasyPay/store', [
        'as' => 'Api500EasyPay.store',
        'uses' => 'Api500EasyPayController@store',
    ]);
});


