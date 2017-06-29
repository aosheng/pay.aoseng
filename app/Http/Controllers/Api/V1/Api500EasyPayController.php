<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Services\Api\V1\Api500EasyPayService;

use Log;

class Api500EasyPayController extends BaseController
{
    protected $payService;

    public function __construct(Api500EasyPayService $Api500EasyPayService)
    {
        $this->payService = $Api500EasyPayService;
    }

    public function index() 
    {
        return 'test';
    }

    public function store(Request $request)
    {   
        //Log::info('get request = ' . print_r($request->all(), true));
        
        $params['config']['merNo'] = 'QYF201705260107';
        $params['config']['signKey'] = '2566AE677271D6B88B2476BBF923ED88';
        $params['config']['encKey'] = 'GiWBZqsJ4GYZ8G8psuvAsTo3';
        $params['config']['payUrl'] = 'http://47.90.116.117:90/api/pay.action';
        $params['config']['remitUrl'] = 'http://47.90.116.117:90/api/remit.action';

        $params['pay']['version'] = 'V2.0.0.0';
        $params['pay']['merNo'] = $params['config']['merNo']; 
        $params['pay']['netway'] = 'WX';
        $params['pay']['random'] = (string) rand(1000,9999);
        $params['pay']['orderNum'] = date('YmdHis') . rand(1000,9999);
        $params['pay']['amount'] = '100';
        $params['pay']['goodsName'] = '测试支付';
        $params['pay']['charset'] = 'utf-8';
        $params['pay']['callBackUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/api/Api500EasyPay/pay_callback';
        $params['pay']['callBackViewUrl'] =  "";
        $params = json_encode($params);
        
        return $this->payService->send($params);
        
        //$this->payService->send($request->all());              
    }
    // 缺查帳 url
    public function check(Request $request)
    {
        $params['config']['merNo'] = 'QYF201705260107';
        $params['config']['signKey'] = '2566AE677271D6B88B2476BBF923ED88';
        $params['config']['encKey'] = 'GiWBZqsJ4GYZ8G8psuvAsTo3';
        $params['config']['payUrl'] = 'http://47.90.116.117:90/api/pay.action';
        $params['config']['remitUrl'] = 'http://47.90.116.117:90/api/remit.action';
        
        $params['pay']['merNo'] = $params['config']['merNo']; 
        $params['pay']['netway'] = 'WX';    
        $params['pay']['orderNum'] = '201706261146034961';
        $params['pay']['amount'] = '1000';
        $params['pay']['goodsName'] = '测试支付';
        $params['pay']['payDate'] = '2017-06-26'; 
        $params['pay']['sign'] = '45782DA8CF84E73B53BBE50DCAF00676';
        $params = json_encode($params);

        $this->payService->pay_check_status($params);
    }

    public function pay_call_back(Request $request)
    {
        $params['merNo'] = 'QYF201705260107';
        $params['netway'] = 'WX';
        $params['orderNum'] = '201706300121146902';
        $params['amount'] = '100';
        $params['goodsName'] = '测试支付';
        $params['payResult'] = '00';
        $params['payDate'] = '20170629033404';
        $params['sign'] = '495EF976F0F3DCB9919CA294DAFD74DC'; 
        
        $params = json_encode($params);
        //$params = json_encode($request->all());

        Log::info('get request = ' . print_r($params, true));
        
        $this->payService->pay_call_back($params);
    }
}
