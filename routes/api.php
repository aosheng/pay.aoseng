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
    // 500EasyPay 輕易付
    // $api->post('Api500EasyPay', [
    //     'as' => 'Api500EasyPay.index',
    //     'uses' => 'Api500EasyPayController@index',
    // ]);
    
    $api->post('Api500EasyPay/store', [
        'as' => 'Api500EasyPay.store',
        'uses' => 'Api500EasyPayController@store',
    ]);
    // $api->post('Api500EasyPay/check', [
    //     'as' => 'Api500EasyPay.check',
    //     'uses' => 'Api500EasyPayController@check',
    // ]);
    $api->post('Api500EasyPay/pay_callback', [
        'as' => 'Api500EasyPay.pay_call_back',
        'uses' => 'Api500EasyPayController@pay_call_back',
    ]);

    //su_hui_bao 速匯寶
    $api->post('SuHuiBao/store', [
        'as' => 'SuHuiBao.store',
        'uses' => 'SuHuiBao@store',
    ]);


});


