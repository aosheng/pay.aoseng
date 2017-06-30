<?php
namespace App\Http\Services\Api\V1;

use Cache;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Redis;
use App\Http\Services\Cache\Api500EasyPayCacheService;
use Log;
use App\jobs\SendCallBackToAdmin;

class Api500EasyPayService
{
    use Helpers;

    protected $third = ['WX', 'ZFB'];

    const GETQRCODETIMES = 8;
    const PAYMENTSERVICE = 'Api500EasyPay';
    const ERRORCODE = '9999';
    const ERRORMSG = '忙线中, 请稍后再试, 或重新整理';

    public function __construct(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        require_once(base_path() . '/resources/ThirdPay/500EasyPay/Util.php');
        require_once(base_path() . '/resources/ThirdPay/500EasyPay/json.php');
        // 時區設置會影響第三方判斷訂單正確性
        date_default_timezone_set("PRC");
        $this->cache_service = $Api500EasyPayCacheService;
        $this->services_json = new \Services_JSON();
        $this->util = new \util();
    }

    public function send($params)
    {
        $params = json_decode($params);

        //$has_qrcode = $this->cache_service->hasQrcode('Api500EasyPay', 'input_base_id', 'QYF201705260107', '201706290756458117');
        $has_qrcode = $this->cache_service->hasQrcode(
            self::PAYMENTSERVICE,
            'input_base_id',
            $params->config->merNo,
            $params->pay->orderNum
        );

        if ($has_qrcode) {
            $base_id = $has_qrcode;
            return self::getResponseQrcode(
                self::PAYMENTSERVICE,
                'response_get_qrcode',
                $base_id,
                self::GETQRCODETIMES
            );
        }
        
        $config = array(
            'merNo' => $params->config->merNo,         #商户号 'qyf201705200001'	
            'signKey' => $params->config->signKey,      #MD5密钥 'AiYLumB03Fingt3R3ULdvFzS'
            'encKey' => $params->config->encKey,        #3DES密钥 'DNSow6CK0MIUUEJrNIziQ1Pm'
            'payUrl' => $params->config->payUrl,        #支付宝或微信地址 'http://47.90.116.117:90/api/pay.action'
            'remitUrl' => $params->config->remitUrl,    #代付地址 'http://47.90.116.117:90/api/remit.action'
	    );
        
        if (!self::config_parames_check($config)) {
            Log::error('# config 配置錯誤 #' . __FILE__ . 'LINE:' . __LINE__);
            return $this->response->error('Config is an error.', 400);
        }

		$pay = array();
		$pay['version'] = $params->pay->version; # 版本号
		$pay['merNo'] = $config['merNo']; #商户号
		$pay['netway'] = $params->pay->netway;  #WX 或者 ZFB
		$pay['random'] = $params->pay->random;  #4位随机数    必须是文本型
		$pay['orderNum'] = $params->pay->orderNum;  #商户订单号
		$pay['amount'] = $params->pay->amount;  #默认分为单位 转换成元需要 * 100   必须是文本型
		$pay['goodsName'] = $params->pay->goodsName;  #商品名称
		$pay['charset'] = $params->pay->charset;  # 系统编码
		$pay['callBackUrl'] = $params->pay->callBackUrl;  #通知地址 可以写成固定
		$pay['callBackViewUrl'] = $params->pay->callBackViewUrl; #暂时没用
        
        if (!self::pay_parames_check($pay)) {
            return $this->response->error('pay parames is an error.', 400);
        }
        
        ksort($pay); #排列数组 将数组已a-z排序
        
        $sign = md5($this->util->json_encode($pay) . $config['signKey']); #生成签名

        $pay['sign'] = strtoupper($sign); #设置签名
        $data = $this->util->json_encode($pay); #将数组转换为JSON格式

        Log::info('# 通知地址 #' . $pay['callBackUrl'] . 'FILE = ' . app_path() . 'LINE:' . __LINE__);
        Log::info('# 提交支付订单 #' . $pay['orderNum'] . 'FILE = ' . app_path() . 'LINE:' . __LINE__);

        $post = array('data' => $data);
        $input_data = ['url' => $config['payUrl'], 'data' => $post, 'config' => $config];
        $base_id = $this->cache_service->setCache(
            self::PAYMENTSERVICE,
            'input_base_id',
            $input_data
        );

        sleep(5);
        return self::getResponseQrcode(
            self::PAYMENTSERVICE,
            'response_get_qrcode',
            $base_id,
            self::GETQRCODETIMES
        );
    }

    public function getResponseQrcode($tags, $type, $base_id, $i)
    {
        sleep(1);
        Log::info('# start get qrcode #' . 'FELE = ' . __FILE__ . 'LINE:' . __LINE__);
        $get_qrcode = $this->cache_service->getResponseQrcode($tags, $type, $base_id);

        if ($get_qrcode == null && $i > 0) {
            $i--;
            self::getResponseQrcode($tags, $type, $base_id, $i);
        }
        Log::info('# get qrcode end #' 
            . 'get_qrcode = ' . print_r($get_qrcode, true)
            . 'FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        if (!$get_qrcode) {
            $get_qrcode['stateCode'] = self::ERRORCODE;
            $get_qrcode['msg'] = self::ERRORMSG;
        }

        return (isset($get_qrcode['qrcodeUrl']))
            ? $get_qrcode['qrcodeUrl']
            : 'error :' . $get_qrcode['stateCode'] . 'msg : ' . $get_qrcode['msg'];
    }

    public function pay($url, $data, $sign_key, $base_id)
    {
        $is_return_data = [];
        $is_return_data = $this->curl_post($url, $data);
        // 将返回json数据转换为数组
        $status = $this->services_json->decode($is_return_data); 
        Log::info('# pay status #'
            . ', status = ' . print_r($status, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
        
        if ($status['stateCode'] !== '00') {
            Log::warning('# 系统错误 #'
                . ', 错误号: ' . $status['stateCode']
                . ', 错误描述: ' . $status['msg']
                . ', FILE = '. __FILE__ . 'LINE:' . __LINE__
            );
            return $status;
        }
        
        if (!self::is_sign($status, $sign_key)) { #验证返回签名数据
            Log::warning('# 返回签名验证失败! #' . 'FILE = ' . __FILE__ . 'LINE:' . __LINE__);
            return $status;
        }

        if ($status['stateCode'] == '00') {
            Log::info('# get qrcode info #' 
                . ', status = ' . print_r($status, true) 
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            // 再想想要不要包進cache_service？
            $this->cache_service->setResponseCache(self::PAYMENTSERVICE, 'response_get_qrcode', $base_id, $status);
            $this->cache_service->deleteListCache(self::PAYMENTSERVICE, 'input_base_id', $base_id);
            $this->cache_service->deleteTagsCache(self::PAYMENTSERVICE, '', $base_id);
        }

        return  $status;
    }

    public function pay_call_back($params)
    {
        $params = $params['data'];

        $base_id = $this->cache_service->getCallBackWaitCache(
            self::PAYMENTSERVICE,
            'call_back_wait',
            $params->merNo,
            $params->orderNum
        );

        if (!$base_id) {
            Log::warning('# base_id null #'
                . '[' . self::PAYMENTSERVICE . '_call_back_wait]'
                . 'merNo' .  $params->merNo
                . 'orderNum' . $params->orderNum
            );
            return false;
        }

        Log::info('base_id = ' . $base_id);
        // 取send的資料 拿sign_key
        $get_send_cache = $this->cache_service->getSendCache(
            self::PAYMENTSERVICE,
            'send',
            $base_id
        ); 
        Log::info('get_send_cache = ' . print_r($get_send_cache, true));
        //$sign_key = '2566AE677271D6B88B2476BBF923ED88';
        $sign_key = $get_send_cache['config']['signKey'];
        
        $call_back['merNo'] = $params->merNo;
        $call_back['netway'] = $params->netway;
        $call_back['orderNum'] = $params->orderNum;
        $call_back['amount'] = $params->amount;
        $call_back['goodsName'] = $params->goodsName;
        $call_back['payResult'] = $params->payResult;
        $call_back['payDate'] = $params->payDate;

        ksort($call_back);
        // 生成签名
        $call_back['sign'] =  strtoupper(md5($this->util->json_encode($call_back) . $sign_key));

        // 验证返回签名数据
        if (!self::is_sign($call_back, $sign_key)) { 
            Log::warning('# 返回签名验证失败! #' 
                . ', call_back = ' . print_r($call_back, true) 
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            return false;
        }
        // 送出時有 *100, callback時要 /100
        $call_back['amount'] = (int) $call_back['amount'] / 100;

        // 第三方call back 訊息存入redis , 發通知給後台接口
        dispatch((new SendCallBackToAdmin($base_id, $call_back))
            ->onQueue('send_call_back_to_admin'));
        // TODO: 是否重複發送, 如何回覆給第三方已接收？
        
        //dd(0);
        return 'success 0';

    }
    // TODO: 查帳 缺第三方url
    public function pay_check_status($params)
    {
        // TODO: 檢查data config sing 再送出查詢
        $params = json_decode($params);
        $config = array(
            'merNo' => $params->config->merNo,         #商户号 'qyf201705200001'	
            'signKey' => $params->config->signKey,      #MD5密钥 'AiYLumB03Fingt3R3ULdvFzS'
            'encKey' => $params->config->encKey,        #3DES密钥 'DNSow6CK0MIUUEJrNIziQ1Pm'
            'payUrl' => $params->config->payUrl,        #支付宝或微信地址 'http://47.90.116.117:90/api/pay.action'
            'remitUrl' => $params->config->remitUrl,    #代付地址 'http://47.90.116.117:90/api/remit.action'
	    );
        
        if (!self::config_parames_check($config)) {
            Log::error('config 配置錯誤' . __FILE__ . 'LINE:' . __LINE__);
            return $this->response->error('Config is an error.', 400);
        }

        $pay = array();
		$pay['merNo'] = $config['merNo']; #商户号
		$pay['netway'] = $params->pay->netway;  #WX 或者 ZFB
		$pay['orderNum'] = $params->pay->orderNum;  #商户订单号
		$pay['amount'] = $params->pay->amount;  #默认分为单位 转换成元需要 * 100   必须是文本型
		$pay['goodsName'] = $params->pay->goodsName;  #商品名称
		$pay['payDate'] = $params->pay->payDate;  # 交易日期（格式：yyyy-MM-dd）

        ksort($pay); #排列数组 将数组已a-z排序
        
		$sign = md5($this->util->json_encode($pay) . $config['signKey']); #生成签名

        //dd([ $pay, $config['signKey'] ]);

		$pay['sign'] = strtoupper($sign); #设置签名
		$data = $this->util->json_encode($pay); #将数组转换为JSON格式

		Log::info('# 查詢支付订单 #' . $pay['orderNum']);

		$post = array('data' => $data);
        // 查帳接口url 未確定?
        $is_return_data = $this->curl_post($config['payUrl'], $data);
        $status = $this->services_json->decode($is_return_data); #将返回json数据转换为数组
        Log::info('# query status #' . print_r($status, true));
    }

    // TODO: 代付
    public function remit()
    {

    }
    // TODO: 代付callback
    public function remit_callback()
    {

    }
    // TODO: 改用Guzzle, PHP HTTP client 
    private function curl_post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);

        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        return $tmpInfo;
    }

    // 以下为检查动作
    private function config_parames_check($config)
    {
		if ($config['merNo'] == '') {
			Log::error('请配置商户号.. config.php。');
			return false;
		}
		
		if ($config['signKey'] == '') {
			Log::error('请配置MD5密钥.. config.php。');
			return false;
		}
		
		if ($config['encKey'] == '') {
			Log::error('请配置3DES密钥.. config.php。');
			return false;
		}
		
		if ($config['payUrl'] == '') {
			Log::error('请配置支付接口.. config.php。');
			return false;
		}
		
		if ($config['remitUrl'] == '') {
			Log::error('请配置代付接口.. config.php。');
			return false;
		}

		return true;	
	}
    
    private function pay_parames_check($parames)
    {
        if (empty($parames['version'])) {
            Log::error('请配置版本号');
			return false;
        }

        if (!in_array($parames['netway'], $this->third)) {
            Log::error('支付选择不正确');
			return false;
        }
       
        if ($parames['amount'] < 0) {
            Log::error('金额不正确');
			return false;
        }

        if (empty($parames['goodsName'])) {
            Log::error('请设定商店名');
			return false;
        }

        if ($parames['charset'] != 'utf-8') {
            Log::error('请设定utf-8');
			return false;
        }

        if (empty($parames['callBackUrl'])) {
            Log::error('请设定回调网址');
			return false;
        }

        return true;
    }
    // 效验服务器返回数据
    private function is_sign($row, $signKey) { 
        // 保留签名数据
        $r_sign = $row['sign'];
        $arr = array();
            foreach ($row as $key => $v) {
                // 删除签名
                if ($key !== 'sign') {
                    $arr[$key] = $v;
                }
            }
        ksort($arr);
        # 生成签名
        $sign = strtoupper(md5($this->util->json_encode($arr) . $signKey));
        if ($sign == $r_sign) {
            return true;
        } else {
            return false;
        }
    }
}