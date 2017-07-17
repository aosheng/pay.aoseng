<?php
namespace App\Http\Services\Api\V1;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\Models\EasyPaySend;
use App\jobs\SendCallBackToAdmin;
use Cache;
use Crypt;
use Dingo\Api\Routing\Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Redis;
use Log;

class Api500EasyPayService
{
    use Helpers;

    protected $third = ['WX', 'ZFB', 'ZFB_WAP'];

    const GETQRCODETIMES = 8;
    const PAYMENTSERVICE = 'Api500EasyPay';
    const ERRORCODE = '9999';
    const ERRORMSG = '忙线中, 请稍后再试, 或重新整理';

    public function __construct(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        require_once(base_path() . '/resources/ThirdPay/500EasyPay/Util.php');
        require_once(base_path() . '/resources/ThirdPay/500EasyPay/json.php');
        require_once(base_path() . '/resources/ThirdPay/500EasyPay/Des3.class.php');
        // 時區設置會影響第三方判斷訂單正確性
        date_default_timezone_set("PRC");
        $this->cache_service = $Api500EasyPayCacheService;
        $this->services_json = new \Services_JSON();
        $this->util = new \util();
    }

    public function send($params)
    {
        $params = json_decode($params);
        
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
            'sCorpCode' => $params->config->sCorpCode,  
            'sOrderID' => $params->config->sOrderID,
            'iUserKey' => $params->config->iUserKey,
            'payment' => $params->config->payment,

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
    
    // TODO: 代付 目前已关闭
    public function send_to_pay($params)
    {
        $params = json_decode($params);
        dd($params);
        $des = new \DES3($params->config->encKey);

        $to_pay['version'] = $params->to_pay->version;
        $to_pay['merNo'] = $params->config->merNo;            
        $to_pay['orderNum'] = $params->to_pay->orderNum;
        $to_pay['amount'] =  $des->encrypt($params->to_pay->amount * 100);
        $to_pay['bankCode'] =  $params->to_pay->bankCode;
        $to_pay['bankAccountName'] = $des->encrypt($params->to_pay->bankAccountName);
        $to_pay['bankAccountNo'] = $des->encrypt($params->to_pay->bankAccountNo);
        $to_pay['charset'] = 'utf-8';
        $to_pay['callBackUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/api/Api500EasyPay/to_pay_callback';
        ksort($to_pay);

        $sign = md5($this->util->json_encode($to_pay) . $config['signKey']); #生成签名
        $to_pay['sign'] = strtoupper($sign); #设置签名
		$data = $this->util->json_encode($to_pay); #将数组转换为JSON格式

        Log::info('通知地址：' . $pay['callBackUrl']);
		Log::info('提交代付订单：' . $pay['orderNum']);
		$post = array('data' => $data);
		$return = self::curl_post($config['remitUrl'], $post); #提交订单数据
        $row = $json->decode($return); #将返回json数据转换为数组
		
        if ($row['stateCode'] !== '00'){
			 Log::info('系统错误,错误号：' . $row['stateCode'] . '错误描述：' . $row['msg']);
			echo '系统维护中.';
			exit();
		} else {
			if (is_sign($row,$config['signKey'])){ #验证返回签名数据
				if ($row['stateCode'] == '00') {
					$stateCode = $row['stateCode'];
 					$msg = $row['msg'];
 					$orderNum = $row['orderNum'];
 					$amount = $row['amount'];
 					$amount = $amount / 100;
 					$string = '创建代付成功!订单号：' . $orderNum . ' 系统消息：' . $msg . ' 代付金额：' . $amount;
					Log::info($string);			
					echo $string;
					exit();
				}
			}else{
			    Log::info('返回签名验证失败!');
			}
			
		}
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
        
        return $get_qrcode;
        // return (isset($get_qrcode['qrcodeUrl']))
        //     ? $get_qrcode['qrcodeUrl']
        //     : 'error :' . $get_qrcode['stateCode'] . 'msg : ' . $get_qrcode['msg'];
    }

    public function pay($url, $data, $sign_key, $base_id)
    {
        $is_return_data = [];
        $is_return_data = $this->guzzle_http($url, $data);
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
        }
        
        if (!self::is_sign($status, $sign_key)
            && $status['stateCode'] === '00') { #验证返回签名数据
            Log::warning('# 返回签名验证失败! #' . 'FILE = ' . __FILE__ . 'LINE:' . __LINE__);
            $status['stateCode'] = '999';
            $status['msg'] = '返回签名验证失败';
        }
        // 再想想要不要包進cache_service？
        $this->cache_service->setResponseCache(self::PAYMENTSERVICE, 'response_get_qrcode', $base_id, $status);
        $this->cache_service->deleteListCache(self::PAYMENTSERVICE, 'input_base_id', $base_id);
        $this->cache_service->deleteTagsCache(self::PAYMENTSERVICE, '', $base_id);
   
        Log::info('# get qrcode status #' 
            . ', status = ' . print_r($status, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        return $status;
    }

    public function pay_call_back($params)
    {      
         $params = json_decode($params); # test
        //$params = json_decode($params['data']);
        
        Log::info('params =>' . print_r($params, true));

        // 確認是否已發過
        $check_call_back = $this->cache_service->checkCallBackCache(
            self::PAYMENTSERVICE,
            'save_call_back',
            $params->merNo,
            $params->orderNum
        );
        
        if ($check_call_back) {
            Log::warning('# call_back saved #'
                . ', [Api500EasyPay_save_call_back]'
                . ', merNo : ' . $params->merNo
                . ', orderNum : ' . $params->orderNum
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            return false;
        }

        $base_id = $this->cache_service->getCallBackWaitCache(
            self::PAYMENTSERVICE,
            'wait_call_back',
            $params->merNo,
            $params->orderNum
        );

        if (!$base_id) {
            Log::warning('# base_id null #'
                . '[' . self::PAYMENTSERVICE . '_wait_call_back]'
                . ', merNo' .  $params->merNo
                . ', orderNum' . $params->orderNum
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            return false;
        }

        Log::info('base_id = ' . $base_id);
        
        $sign_key = false;    
        // 取send的資料 拿sign_key
        $get_send_cache = $this->cache_service->getSendCache(
            self::PAYMENTSERVICE,
            'send',
            $base_id
        ); 

        if (!$get_send_cache) {
            $get_send_cache = EasyPaySend::where('base_id', $base_id)->first();
            
            if (!$get_send_cache) {
                Log::warning('# get_send_cache null #'
                    . '[' . self::PAYMENTSERVICE . '_send]'
                    . ', base_id' .  $base_id
                    . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
                );
                return false;
            }
            $sign_key = Crypt::decrypt($get_send_cache->config_signKey);
            $amount = $get_send_cache->amount;
        }

        Log::info('# get_send_cache # ' . print_r($get_send_cache, true));

        if (!$sign_key) {
            $sign_key = $get_send_cache['config']['signKey'];
            $send_third_data = json_decode($get_send_cache['data']['data']);
            $amount = $send_third_data->amount;
        }

        if ($amount != $params->amount) {
            Log::warning('# 金額不符 #'
                . '[' . self::PAYMENTSERVICE . '_send]'
                . ', send_third_data = ' . $send_third_data->amount
                . ', call back params = ' . $params->amount
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            return false;
        } 

        $call_back['merNo'] = $params->merNo;
        $call_back['netway'] = $params->netway;
        $call_back['orderNum'] = $params->orderNum;
        $call_back['amount'] = $params->amount;
        $call_back['goodsName'] = $params->goodsName;
        $call_back['payResult'] = $params->payResult;
        $call_back['payDate'] = $params->payDate;

        ksort($call_back);

        $call_back['sign'] = $params->sign;

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
        Log::info('# To SendCallBackToAdmin job success #');    
            
        return 0;

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

    private function guzzle_http($url, $data) 
    {
        Log::info('# guzzle_http start #' 
            . ', url =' . print_r($url, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        $client = new \GuzzleHttp\Client();
        $handlers['User-Agent'] = 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)';
        $handlers['allow_redirects'] = false;
        $handlers['Content-Type'] = 'application/json';
        $handlers['http_errors'] = false;

        $options['handlers'] = $handlers;
        $options['form_params'] = $data;
        
        try {
            $res = $client->post($url, $options);
            Log::info('# guzzle_http end #' 
                . 'getBody = ' . $res->getBody()
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
                
            );
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            Log::error('# guzzle_http end #' 
                . ', getRequest = ' . $exception->getRequest()
                . ', getResponse = ' . $exception->getResponse()
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
        }
        return $res->getBody();
    }

    // 以下为检查动作
    private function config_parames_check($config)
    {
		if ($config['sCorpCode'] == '') {
            Log::error('请配置盤口号.. config');
        	return false;
		}

        if ($config['sOrderID'] == '') {
            Log::error('请配置訂單号.. config');
			return false;
		}

        if ($config['iUserKey'] == '') {
            Log::error('请配置UserKey.. config');
			return false;
		}

        if ($config['payment'] == '') {
            Log::error('请配置商户号.. config');
			return false;
		}
        
        if ($config['merNo'] == '') {
			Log::error('请配置商户号.. config');
			return false;
		}
		
		if ($config['signKey'] == '') {
			Log::error('请配置MD5密钥.. config');
			return false;
		}
		
		if ($config['encKey'] == '') {
			Log::error('请配置3DES密钥.. config');
			return false;
		}
		
		if ($config['payUrl'] == '') {
			Log::error('请配置支付接口.. config');
			return false;
		}
		
		if ($config['remitUrl'] == '') {
			Log::error('请配置代付接口.. config');
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
        if (!isset($row['sign'])) {
            return false;
        }
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