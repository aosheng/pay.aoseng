<?php

namespace App\Http\Controllers\api\V1;

use Illuminate\Http\Request;
use App\Http\Services\Api\V1\SyHuiBaoService;
use Log;

class SuHuiBaoController extends BaseController
{
    public function __construct(SyHuiBaoService $SyHuiBaoService)
    {
        $this->payService = $SyHuiBaoService;
    }

    public function store(Request $request)
    {   
        //Log::info('get request = ' . print_r($request->all(), true));   
        $params['config']['merchant_code'] = 'pay';     
        $params['config']['service_type'] = 'QYF201705260107';
        $params['config']['notify_url'] = '2566AE677271D6B88B2476BBF923ED88';
        $params['config']['encKey'] = 'GiWBZqsJ4GYZ8G8psuvAsTo3';
        $params['config']['payUrl'] = 'http://47.90.116.117:90/api/pay.action';
        $params['config']['remitUrl'] = 'http://47.90.116.117:90/api/remit.action'; # 目前已关闭

        if ($request['config']['payment'] === 'pay') { 
            $request['pay']['version'] = 'V2.0.0.0';
            $request['pay']['merNo'] = $request['config']['merNo']; 
            $request['pay']['netway'] = 'WX';
            $request['pay']['random'] = (string) rand(1000, 9999);
            $request['pay']['orderNum'] = date('YmdHis') . rand(1000, 9999);
            $request['pay']['amount'] = '100';
            $request['pay']['goodsName'] = '测试支付WX';
            $request['pay']['charset'] = 'utf-8';
            $request['pay']['callBackUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/api/Api500EasyPay/pay_callback';
            $request['pay']['callBackViewUrl'] = "";
            $request = json_encode($request);
            $response_data = $this->payService->send($request);
            
            if ($response_data['stateCode'] !== '00') {
                return $this->response->error(json_encode($response_data), 400);
            }
            return $this->response->array($response_data)->setStatusCode(200);
        }         
    }
}
