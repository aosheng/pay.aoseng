<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Services\Api\V1\Api500EasyPayService;

use Log;

/** js
 * @apiDefine Request_templte
 * @apiSuccess (Request templte) {json} config 商家基本設定
 * @apiSuccess (Request templte) {json} pay 商家訂單資訊
 * @apiSuccessExample {json} Request templte 
 *  POST /api/Api500EasyPay/store 
 *  {
 *      "config":
 *          {
 *              "payment":"pay",
 *              "merNo":"QYF201705260107",
 *              "signKey":"2566AE677271D6B88B2476BBF923ED88",
 *              "encKey":"GiWBZqsJ4GYZ8G8psuvAsTo3",
 *              "payUrl":"http:\/\/47.90.116.117:90\/api\/pay.action",
 *              "remitUrl":"http:\/\/47.90.116.117:90\/api\/remit.action"
 *          },
 *      "pay":
 *          {
 *              "version":"V2.0.0.0",
 *              "merNo":"QYF201705260107",
 *              "netway":"WX","random":"7453",
 *              "orderNum":"201707031204515715",
 *              "amount":"100",
 *              "goodsName":"\u6d4b\u8bd5\u652f\u4ed8WX",
 *              "charset":"utf-8",
 *              "callBackUrl":"http:\/\/pay.aosheng.com\/api\/Api500EasyPay\/pay_callback",
 *              "callBackViewUrl":""
 *           }
 *  }
 */
 /** 
 * @apiDefine CODE_200
 * @apiSuccess (Reponse 200) {number} status_code 200
 * @apiSuccess (Reponse 200) {json} data  get qrcode 
 * @apiSuccessExample {json} Response 200 
 *   HTTP/1.1 200 OK POST /api/Api500EasyPay/store
 *   {
 *      "merNo": "QYF201705260107",
 *      "msg": "提交成功",
 *      "orderNum": "201707031024005063",
 *      "qrcodeUrl": "weixin://wxpay/bizpayurl?pr=XWMdWpG",
 *      "sign": "15811B8CDE231D180776C8E7B352B026",
 *      "stateCode": "00"
 *   }
 */
/**
 * @apiDefine CODE_400
 * @apiSuccess (Response 400) {number} status_code 400
 * @apiSuccess (Response 400) {json} message error description
 * @apiSuccessExample {json} Response 400 
 *   HTTP/1.1 400 Internal Server Error
 *   {
 *      "status_code": "400",
 *      "message": {
 *          "stateCode": "9999",
 *          "msg": "忙线中, 请稍后再试, 或重新整理"
 *      }
 *   }
 */

/**
 * @apiDefine Config
 *
 * @apiParam (config) {string} [payment="pay"]  支付類型
 * @apiParam (config) {string} payment_service  支付商別名  ex: 500EasyPay
 * @apiParam (config) {string} merNo 商戶號
 * @apiParam (config) {string} signKey MD5密鑰
 * @apiParam (config) {string} encKey 3DES密鑰
 * @apiParam (config) {string} payUrl 支付寶或微信地址
 * @apiParam (config) {string} remitUrl 代付地址
 */

/**
 * @apiDefine Pay
 *
 * @apiParam (pay) {string} version="V2.0.0.0"  版本號
 * @apiParam (pay) {string} merNo  商戶號
 * @apiParam (pay) {string} [netway] WX(微信) 或者 ZFB(支付寶)
 * @apiParam (pay) {string} random 4位隨機數 必須是文本型 ex: (string) rand(1000,9999) 
 * @apiParam (pay) {string} orderNum 商户訂單號 ex: date('YmdHis') . rand(1000,9999)  
 * @apiParam (pay) {string} amount 默認分為單位 轉换成元需要 * 100   必需是文本型
 * @apiParam (pay) {string} goodsName 商品名稱
 * @apiParam (pay) {string} charset="utf-8" 系统編碼
 * @apiParam (pay) {string} callBackUrl 通知地址
 * @apiParam (pay) {string} callBackViewUrl 暫時沒用
 */

/**
 * @api {POST} /api/Api500EasyPay/store /api/Api500EasyPay/store
 * @apiName 500EasyPay
 * @apiGroup 500EasyPay
 * @apiVersion 1.0.0
 * @apiDescription 500輕易付 發送訂單
 * @apiPermission POST
 * @apiSampleRequest http://testpayaosheng.azurewebsites.net/api/Api500EasyPay/store
 *
 * @apiParam {json} config 商家基本設定
 * @apiParam {json} pay 訂單資訊
 * @apiUse Config
 * @apiUse Pay
 * 
 * @apiUse Request_templte
 * @apiUse CODE_200
 * @apiUse CODE_400
 */

class Api500EasyPayController extends BaseController
{
    protected $payService;

    public function __construct(Api500EasyPayService $Api500EasyPayService)
    {
        $this->payService = $Api500EasyPayService;
    }

    public function store(Request $request)
    {   
        //Log::info('get request = ' . print_r($request->all(), true));   
        $params['config']['payment'] = 'pay';     
        $params['config']['merNo'] = 'QYF201705260107';
        $params['config']['signKey'] = '2566AE677271D6B88B2476BBF923ED88';
        $params['config']['encKey'] = 'GiWBZqsJ4GYZ8G8psuvAsTo3';
        $params['config']['payUrl'] = 'http://47.90.116.117:90/api/pay.action';
        $params['config']['remitUrl'] = 'http://47.90.116.117:90/api/remit.action'; # 目前已关闭

        // 支付 (ZFB 有时间性会失效, 如有人已刷过无法再给人刷)
        if ($params['config']['payment'] === 'pay') { 
            $params['pay']['version'] = 'V2.0.0.0';
            $params['pay']['merNo'] = $params['config']['merNo']; 
            $params['pay']['netway'] = 'WX';
            $params['pay']['random'] = (string) rand(1000,9999);
            $params['pay']['orderNum'] = date('YmdHis') . rand(1000,9999);
            $params['pay']['amount'] = '100';
            $params['pay']['goodsName'] = '测试支付WX';
            $params['pay']['charset'] = 'utf-8';
            $params['pay']['callBackUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/api/Api500EasyPay/pay_callback';
            $params['pay']['callBackViewUrl'] = "";
            $params = json_encode($params);
            $response_data = $this->payService->send($params);
            
            if ($response_data['stateCode'] !== '00') {
                return $this->response->error(json_encode($response_data), 400);
            }
            return $this->response->array($response_data)->setStatusCode(200);
        }

        // 代付 
        if ($params['config']['payment'] === 'to_pay') { 
            $params['to_pay']['version'] = 'V2.0.0.0';
            $params['to_pay']['merNo'] = $params['config']['merNo'];            
            $params['to_pay']['orderNum'] = date('YmdHis') . rand(1000,9999);
            $params['to_pay']['amount'] = '1';
            $params['to_pay']['bankCode'] = 'ICBC';
            $params['to_pay']['bankAccountName'] = '梁铭光';
            $params['to_pay']['bankAccountNo'] = '6212261405007142466';
            $params['to_pay']['charset'] = 'utf-8';
            $params['to_pay']['callBackUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/api/Api500EasyPay/to_pay_callback';
            $params = json_encode($params);
            
            return $this->payService->send_to_pay($params);
        }           
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
    /**
     * @apiDefine Data
     *
     * @apiParam (data) {string} merNo  商戶號
     * @apiParam (data) {string} [netway] 支付网关(支付宝填写ZFB,微信填写WX)
     * @apiParam (data) {string} orderNum 商户訂單號  
     * @apiParam (data) {string} amount 金额（单位：分）
     * @apiParam (data) {string} goodsName 商品名稱
     * @apiParam (data) {string} payResult 支付状态，00表示成功
     * @apiParam (data) {string} payDate 支付时间，格式：yyyyMMddHHmmss
     * @apiParam (data) {string} sign 签名（字母大写）
     */
    /**
     * @api {POST} /api/Api500EasyPay/pay_callback /api/Api500EasyPay/pay_callback
     * @apiName 500EasyPay_callback
     * @apiGroup 500EasyPay
     * @apiVersion 1.0.0
     * @apiDescription 500輕易付 callback 接收付款成功或失敗訊息
     * @apiPermission POST
     * @apiSampleRequest http://testpayaosheng.azurewebsites.net/api/Api500EasyPay/pay_callback
     *
     * @apiParam {json} data 第三方回傳訊息
     * @apiUse Data
     * 
     */
    public function pay_call_back(Request $request)
    {
        // test params
        // require_once(base_path() . '/resources/ThirdPay/500EasyPay/Util.php');
        // $this->util = new \util();
        
        // $sign_key = '2566AE677271D6B88B2476BBF923ED88';
        // $params['merNo'] = 'QYF201705260107';
        // $params['netway'] = 'WX';
        // $params['orderNum'] = '201706301409577691';
        // $params['amount'] = '100';
        // $params['goodsName'] = '测试支付';
        // $params['payResult'] = '00';
        // $params['payDate'] = '20170630140957';
        // ksort($params);
        // $params['sign'] = strtoupper(md5($this->util->json_encode($params) . $sign_key)); 
        // $params = json_encode($params); # test 
        // Log::info('get request = ' . print_r($params, true)); # test
       
        Log::info('get request = ' . print_r($request->all(), true));
        
        // $this->payService->pay_call_back($params); # test
        $this->payService->pay_call_back($request->all());
    }
}
