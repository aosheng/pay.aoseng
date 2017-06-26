<?php
namespace App\Http\Services\Api\V1;

use Cache;
use Dingo\Api\Routing\Helpers;
use Jenssegers\Date\Date;
use Illuminate\Support\Facades\Redis;
use App\Http\Services\Cache\Api500EasyPayCacheService;

use Log;

class Api500EasyPayService
{
    use Helpers;

    protected $third = ['WX', 'ZFB'];

    public function __construct(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        require_once(base_path() . '/resources/ThirdPay/500EasyPay/Util.php');
        require_once(base_path() . '/resources/ThirdPay/500EasyPay/json.php');

        date_default_timezone_set("PRC");
        $this->cache_service = $Api500EasyPayCacheService;
        $this->services_json = new \Services_JSON();
        $this->util = new \util();

    }

    public function send($params)
    {
        //dd($_SERVER['HTTPS'] ? 'https' : 'http' . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        //dd(json_decode($params));

        $params = json_decode($params);
        $config = array(
            'merNo' => $params->config->merNo,         #商户号 'qyf201705200001'	
            'signKey' => $params->config->signKey,      #MD5密钥 'AiYLumB03Fingt3R3ULdvFzS'
            'encKey' => $params->config->encKey,        #3DES密钥 'DNSow6CK0MIUUEJrNIziQ1Pm'
            'payUrl' => $params->config->payUrl,        #支付宝或微信地址 'http://47.90.116.117:90/api/pay.action'
            'remitUrl' => $params->config->remitUrl,    #代付地址 'http://47.90.116.117:90/api/remit.action'
	    );
        
        if (!self::config_parames_check($config)) {
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
        //dd($pay);
        if (!self::pay_parames_check($pay)) {
            return $this->response->error('pay parames is an error.', 400);
        }
        
        ksort($pay); #排列数组 将数组已a-z排序
        
		$sign = md5($this->util->json_encode($pay) . $config['signKey']); #生成签名

        //dd([ $pay, $config['signKey'] ]);

		$pay['sign'] = strtoupper($sign); #设置签名
		$data = $this->util->json_encode($pay); #将数组转换为JSON格式

		self::write_log('通知地址：' . $pay['callBackUrl']);
		self::write_log('提交支付订单：' . $pay['orderNum']);

		$post = array('data' => $data);
 
        // $base_id = uniqid();
        $input_data = ['url' => $config['payUrl'], 'data' => $post, 'config' => $config];
        
        $base_id = $this->cache_service->setCache('Api500EasyPay_input', $input_data);

        // to get response qrcode
        sleep(5);
        $get_qrcode = self::toGetResponseQrcode('Api500EasyPay_response', 'get_qrcode', $base_id, 5);
        
        return  (isset($get_qrcode['qrcodeUrl'])) ? $get_qrcode['qrcodeUrl'] : 'error :' . $get_qrcode['stateCode'] . 'msg : ' . $get_qrcode['msg'];
        //$this->pay($config['payUrl'], $post, $config['signKey']);
    }

    public function toGetResponseQrcode($tags, $type, $base_id, $i)
    {
        sleep(1);
        Log::info('start get qrcode' . date('Y-m-d H:i:s'));
        $qr_code = $this->cache_service->toGetResponseQrcode($tags, $type, $base_id);

        if ($qr_code == null && $i > 0) {
            $i--;
            self::toGetResponseQrcode($tags, $type, $base_id, $i);
        }
        Log::info('get qrcode' . date('Y-m-d H:i:s'));
        
        return $qr_code;
    }


    public function pay($url, $data, $sign_key, $base_id)
    {
        //$is_return_data = false;
        $is_return_data = [];
        $is_return_data = $this->curl_post($url, $data);
        $status = $this->services_json->decode($is_return_data); #将返回json数据转换为数组
        Log::info('pay return' . print_r($status, true));
        if ($status['stateCode'] !== '00') {
            //self::write_log('系统错误,错误号：' . $status->stateCode . '错误描述：' . $status->msg);
            Log::warning('系统错误,错误号：' . $status['stateCode'] . '错误描述：' . $status['msg']);
            //return $this->response->error('系统错误,错误号：' . $status['stateCode'] . '错误描述：' . $status['msg'], 400);
            return $status;
        }
        
        if (!self::is_sign($status, $sign_key)) { #验证返回签名数据
            //self::write_log('返回签名验证失败!');
            Log::warning('返回签名验证失败!');
            //return $this->response->error('返回签名验证失败!', 403);
            return $status;
        }
		
        // if ($status['stateCode'] == '00') {
        //     $stateCode = $status['stateCode'];
        //     $msg = $status['msg'];
        //     $orderNum = $status['orderNum'];
        //     $amount = $status['amount'];
        //     $amount = $amount / 100;
        //     $string = '创建代付成功!订单号：' . $orderNum . ' 系统消息：' . $msg . ' 代付金额：' . $amount;
        //     self::write_log($string);		
        //     return $this->response->array($status);
        //     //return $this->response->item($status, new Api500EasyPayTransformer);	
        // }
        if ($status['stateCode'] == '00') {
            Log::info('get qrcode info' . print_r($status, true));
            $this->cache_service->setResponseCache('Api500EasyPay', 'response_get_qrcode', $base_id, $status);
            $this->cache_service->deleteCache('Api500EasyPay_input', 'base_id', $base_id);
            $this->cache_service->deleteTagsCache('Api500EasyPay', 'input', $base_id);
        }

        return  $status;
    }

    public function pay_callback()
    {

    }

    public function pay_check_status()
    {
        // TODO: 檢查data config sing 再送出查詢
    }

    public function remit()
    {

    }

    public function remit_callback()
    {

    }

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
		if ($config['merNo'] == ''){
			self::write_log('请配置商户号.. config.php。');
			return false;
		}
		
		if ($config['signKey'] == ''){
			self::write_log('请配置MD5密钥.. config.php。');
			return false;
		}
		
		if ($config['encKey'] == ''){
			self::write_log('请配置3DES密钥.. config.php。');
			return false;
		}
		
		if ($config['payUrl'] == ''){
			self::write_log('请配置支付接口.. config.php。');
			return false;
		}
		
		if ($config['remitUrl'] == ''){
			self::write_log('请配置代付接口.. config.php。');
			return false;
		}

		return true;	
	}
    
    private function pay_parames_check($parames)
    {
        if (empty($parames['version'])) {
            self::write_log('请配置版本号');
			return false;
        }

       if (!in_array($parames['netway'], $this->third)) {
            self::write_log('支付选择不正确');
			return false;
       }
       
        if ($parames['amount'] < 0) {
            self::write_log('金额不正确');
			return false;
        }

        if (empty($parames['goodsName'])) {
            self::write_log('请设定商店名');
			return false;
        }

        if ($parames['charset'] != 'utf-8') {
            self::write_log('请设定utf-8');
			return false;
        }

        if (empty($parames['callBackUrl'])) {
            self::write_log('请设定回调网址');
			return false;
        }

        return true;	
    }
    
    private function is_sign($row, $signKey) { #效验服务器返回数据
        $r_sign = $row['sign']; #保留签名数据
        $arr = array();
            foreach ($row as $key=>$v) {
                if ($key !== 'sign') { #删除签名
                    $arr[$key] = $v;
                }
            }
        ksort($arr);
        $sign = strtoupper(md5($this->util->json_encode($arr) . $signKey)); #生成签名
        if ($sign == $r_sign) {
            return true;
        } else {
            return false;
        }
    }

    private function write_log($str){ #输出LOG日志
		$str = date('Y-m-d H:i:s') . ' '  . $str . "\r\n";
		$data = file_put_contents("test.log", $str,FILE_APPEND);
	}
}