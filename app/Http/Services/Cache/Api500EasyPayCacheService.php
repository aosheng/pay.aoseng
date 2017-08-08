<?php
namespace App\Http\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;
use App\Http\Cache\BaseCacheHelper;

class Api500EasyPayCacheService  extends BaseCacheHelper
{
    const SENDLISTLIMIT = 200;
    const TYPEWAITCALLBACK = 'wait_call_back';
    const TYPETIMESTAMP = 'timestamp';
    
    public function __construct()
    {
      parent::setPrtefix('EasyPay');
    }
    /**
     * Input data save redis
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(input_base_id)
     * @param [array] $data
     * @return Base_id
     */
    public function setInputGetBaseId($tags, $type, $data)
    {
        $base_id = parent::getBaseId($tags);
        
        parent::setListCache($tags, $type, $base_id);

        // 寫入唯一key做之後判別
        $data = array_merge($data, array('base_id' => $base_id));
        parent::setTagsCache($tags, $type, $base_id, $data);
        self::setTimestamp($tags, $base_id);
        Log::info('# setInputGetBaseId #'  
            . ', ['. $tags . '_' . $type . ']' 
            . ', base_id = ' . $base_id 
            . ', data = ' . print_r($data, true) 
            . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__
        );

        return $base_id;
    }
    /**
     * 取出存入的訂單, 把 base_id 合進去
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(input_base_id)
     * @return array
     */
    public function getInputListData($tags, $type)
    {
        $tasks = parent::getListCache($tags, $type);
        $return_data = [];

        if (!$tasks) {
            // Log::info('# getCache tasks null #'
            //     . ', tags = ' . $tags
            //     . ', type = ' . $type
            //     . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            // );
            return false;
        }

        Log::info('# get cache tasks #' 
            . ', tasks = ' . print_r($tasks, true)
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        foreach ($tasks as $task_base_id) {
            $get_task = parent::getCacheValue($tags, $type, $task_base_id);

            if (!$get_task) {
                Log::warning('# get cache warning #' 
                    . ', ['. $tags . '_' . $type . ']' 
                    . ', base_id = ' . $task_base_id 
                    . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
                );
                continue;
            }
            array_push($return_data, $get_task);
        }

        Log::info('# getCache_data #' 
            . ', return_data = ' . print_r($return_data, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        return $return_data;
    }
    /**
     * 儲存送去第三方的資料
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(send)
     * @param [String] $base_id 唯一key
     * @param [array] $data
     * @return null
     */
    public function setSendCache($tags, $type, $base_id, $data)
    {
        parent::setListCache($tags, $type, $base_id);
        parent::setTagsCache($tags, $type, $base_id, $data);    
        
        Log::info('# setSendCache success #'
            . ', ['. $tags . '_' . $type .']'
            . ', base_id = ' . $base_id 
            . ', data = ' . print_r($data, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }
    /**
     *  get send list
     * @param [type] $tags 支付別名
     * @param [type] $type 組別(send)
     * @return boolean
     */
    public function getSendList($tags, $type)
    {
        return parent::getListCache($tags, $type);
    }
    /**
     * get qrcode 回傳資訊寫入
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(response_get_qrcode)
     * @param [String] $base_id 唯一key
     * @param [array] $data 第三方狀態資料
     * @return void
     */
    public function setResponseCache($tags, $type, $base_id, $data)
    {
        parent::setListCache($tags, $type, $base_id);
        parent::setTagsCache($tags, $type, $base_id, $data);
        
        Log::info('# setResponseCache #'
            . ', ['. $tags . '_' . $type .']'
            . ', base_id = '. $base_id 
            . ', data = ' . print_r($data, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }
    /**
     * 儲存等待付款的資訊
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(wait_call_back)
     * @param [String] $base_id 唯一key
     * @param [array] $data 第三方狀態資料
     * @return void
     * 第三方無法帶 base_id 過去, 先用商戶號 + 訂單做標記
     */
    public function setCallBackWaitCache($tags, $type, $base_id, $data)
    {  
        parent::setListCache($tags, $type, $data['merNo'] . '_' . $data['orderNum']);
        parent::setTagsCache($tags, $type, $data['merNo'] . '_' . $data['orderNum'], $base_id);
        self::setTimestamp($tags, $data['merNo'] . '_' . $data['orderNum']);
        Log::info('# setCallBackWaitCache #' 
            . ', ['. $tags . '_' . $type .']' 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }
    /**
     * 儲存第三方回傳付款訊息
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(save_call_back)
     * @param [String] $base_id 唯一key
     * @param [json] $data
     * @return void
     */
    public function saveCallBackCache($tags, $type, $base_id, $data)
    {
        parent::setListCache($tags, $type, $base_id);
        parent::setTagsCache($tags, $type, $base_id, $data);
        Log::info('# save call_back #'
            . ', [' . $tags . '_' . $type . ']'
            . ', data = ' . print_r($data, true)
            . ', base_id = ' . $base_id
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );    
    }
    /**
     * 取得成功支付回傳列表
     * @param [type] $tags 支付別名
     * @param [type] $type 組別(save_call_back)
     * @return array
     */
    public function getSaveCallBackList($tags, $type)
    {
        return parent::getListCache($tags, $type);
    }
    /**
     * 取得取得成功支付資訊
     * @param [type] $tags 支付別名
     * @param [type] $type 組別(save_call_back)
     * @param [type] $base_id 唯一key
     * @return null or json
     */
    public function getSaveCallBack($tags, $type, $base_id)
    {
        return parent::getCacheValue($tags, $type, $base_id);
    }
    /**
     * Wait Call Back function
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(wait_call_back)
     * @param [String] $merNo 商戶號
     * @param [Int] $orderNum 訂單編號
     * @return null or base_id 
     */
    public function getCallBackWaitBaseId($tags, $type, $merNo, $orderNum)
    {
        return parent::getCacheValue($tags, $type, $merNo . '_' . $orderNum);
    }
    /**
     * 紀錄call bcak已存過訊息, 用來停止第三方回調
     * @param [type] $tags $tags 支付別名
     * @param [type] $type 組別(check_call_back)
     * @param [type] $merNo 商戶號
     * @param [type] $orderNum 訂單編號
     * @return void
     */
    public function saveCheckCallBackCache($tags, $type, $merNo, $orderNum)
    {
        parent::setTagsCache($tags, $type, $merNo . '_' . $orderNum, true);
    }
    /**
     * 確認call back 是否已送回
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(check_call_back)
     * @param [String] $merNo 商戶號
     * @param [Int] $orderNum 訂單編號
     * @return null or array
     */
    public function checkCallBackCache($tags, $type, $merNo, $orderNum)
    {
        return parent::getCacheValue($tags, $type, $merNo . '_' . $orderNum);
    }
    /**
     * 取出送至第三方資訊
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(send)
     * @param [String] $base_id 唯一key
     * @return array
     */
    public function getSendCache($tags, $type, $base_id)
    {
        return parent::getCacheValue($tags, $type, $base_id);
    }
    /**
     * 儲存Qrcode base_id
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(qrcode)
     * @param [String] $merNo 商戶號
     * @param [Int] $orderNum 訂單編號
     * @param [String] $value
     * @return void
     */   
    public function saveQrcodeBaseId($tags, $type, $merNo, $orderNum, $value)
    {
        parent::setTagsCache($tags, $type, $merNo . '_' . $orderNum, $value);
    }
    /**
     * qrcode 是否已取回
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(input_base_id)
     * @param [String] $merNo 商戶號
     * @param [Int] $orderNum 訂單編號
     * @return base_id or null
     */
    public function hasQrcodeBaseId($tags, $type, $merNo, $orderNum)
    {
        return parent::getCacheValue($tags, $type, $merNo . '_' . $orderNum);
    }   
    /**
     * 取得qrcode 清單的 base_id
     * @param [type] $tags 支付別名
     * @param [type] $type 組別(response_get_qrcode)
     * @return array
     */
    public function getResponseQrcodeList($tags, $type)
    {
        return parent::getListCache($tags, $type);
    }
    /**
     * 回傳的Qrcode
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(response_get_qrcode)
     * @param [String] $base_id 唯一key
     * @return json
     */
    public function getResponseQrcode($tags, $type, $base_id)
    {
        return parent::getCacheValue($tags, $type, $base_id);
    }
    /**
     * 刪除 Cache
     * @param [String] $tags 支付別名
     * @param [String] $type 組別
     * @param [String] $base_id 唯一key
     * @return void
     */
    public function deleteCache($tags, $type, $base_id)
    {
        parent::deleteListValue($tags, $type, $base_id);
        parent::deleteTagsValue($tags, $type, $base_id);

        Log::info('# Delete Cache success #'
            . ', [' . $tags . '_' . $type . ']'
            . ', base_id = ' . $base_id
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );   
    }
    /**
     * 儲存 base_id timestamp
     * @param [type] $tags 支付別名
     * @param [type] $id 唯一key
     * @return void
     */
    private function setTimestamp($tags, $id)
    {
        parent::setZaddCach($tags, self::TYPETIMESTAMP, time(), $id);
    }
}
