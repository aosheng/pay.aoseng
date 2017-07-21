<?php
namespace App\Http\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;

class Api500EasyPayCacheService
{
    const SURVIVAL_TIME = 1440;
    const SENDLISTLIMIT = 200;
    const TYPEWAITCALLBACK = 'wait_call_back';
    
    public function __construct()
    {
        Cache::store('redis')->setPrefix('500EasyPay');
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
        $base_id = $tags . '_' . uniqid();
        
        Redis::rpush($tags . '_' . $type, $base_id);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($base_id, $data);
        
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
    public function getInputCacheList($tags, $type)
    {
        $tasks = Redis::lrange($tags . '_' . $type, 0, -1);
        $task_data = [];
        $return_data = [];

        if (!$tasks) {
            Log::info('# getCache tasks null #'
                . ', [' . $tags . ']'
                . ', type = ' . $type
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            return false;
        }

        Log::info('# get cache tasks #' 
            . ', tasks = ' . print_r($tasks, true)
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        foreach ($tasks as $task_base_id) {
            $get_task = Cache::store('redis')
                ->tags([$tags . '_' . $type])
                ->get($task_base_id);
            
            if (!$get_task) {
                Log::warning('# get cache warning #' 
                    . ', ['. $tags . '_' . $type . ']' 
                    . ', base_id = ' . $task_base_id 
                    . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
                );
                continue;
            }

            $task_data = array_merge($get_task,
                array('base_id' => $task_base_id)
            );
            array_push($return_data, $task_data);
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
        Redis::rpush($tags . '_' . $type, $base_id);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($base_id, $data);
        
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
        return Redis::lrange($tags . '_' . $type, 0, self::SENDLISTLIMIT);
    }
    /**
     * get qrcode 回傳資訊寫入
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(response_get_qrcode)
     * @param [String] $base_id 唯一key
     * @param [array] $data 第三方狀態資料
     * @return null
     */
    public function setResponseCache($tags, $type, $base_id, $data)
    {
        Redis::rpush($tags . '_' . $type, $base_id);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($base_id, $data);
        
        Log::info('# setResponseCache #'
            . ', ['. $tags . '_' . $type .']'
            . ', base_id = '. $base_id 
            . ', data = ' . print_r($data, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
        // 這做法要再想想(移出去?)
        if ($data['stateCode'] === '00') {
            self::setCallBackWaitCache(
                $tags,
                self::TYPEWAITCALLBACK,
                $base_id,
                $data
            );
        }
    }
    /**
     * 儲存第三方回傳付款訊息
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(save_call_back)
     * @param [String] $base_id 唯一key
     * @param [json] $data
     * @return null
     */
    public function saveCallBackCache($tags, $type, $base_id, $data)
    {
        Redis::rpush($tags . '_' . $type, $base_id);
        
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($base_id, $data);
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
        return Redis::lrange($tags . '_' . $type, 0, self::SENDLISTLIMIT);
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
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($base_id);
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
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($merNo . '_' . $orderNum);
    }
    /**
     * 確認call back 是否已送回
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(save_call_back)
     * @param [String] $merNo 商戶號
     * @param [Int] $orderNum 訂單編號
     * @return null or array
     */
    public function checkCallBackCache($tags, $type, $merNo, $orderNum)
    {
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($merNo . '_' . $orderNum);
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
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($base_id);
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
        return Redis::Get($tags . '_' . $type . '_' . $merNo . '_' . $orderNum);
    }
    /**
     * 取得qrcode 清單的 base_id
     * @param [type] $tags 支付別名
     * @param [type] $type 組別(response_get_qrcode)
     * @return array
     */
    public function getResponseQrcodeList($tags, $type)
    {
        return Redis::lrange($tags . '_' . $type, 0, self::SENDLISTLIMIT);
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
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($base_id);
    }
    /**
     * 刪除 redis list key
     * @param [String] $tags 支付別名
     * @param [String] $type 組別
     * @param [String] $base_id 唯一key
     * @return boolean
     */  
    public function deleteListCache($tags, $type, $base_id)
    {
        return Redis::LREM($tags . '_' . $type, 0, $base_id);
    }
    /**
     * 刪除 redis tags 儲存資料
     * @param [String] $tags 支付別名
     * @param [String] $type 組別
     * @param [String] $base_id 唯一key
     * @return boolean
     */  
    public function deleteTagsCache($tags, $type, $base_id)
    {
        return Cache::store('redis')
            ->tags([$tags. '_' . $type])
            ->forget($base_id);
    }
    /**
     * 儲存等待付款的資訊
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(wait_call_back)
     * @param [String] $base_id 唯一key
     * @param [array] $data 第三方狀態資料
     * @return null
     */
    private function setCallBackWaitCache($tags, $type, $base_id, $data)
    {
        Redis::rpush($tags . '_' . $type, $data['merNo'] . '_' . $data['orderNum']);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($data['merNo'] . '_' . $data['orderNum'], $base_id);

        Log::info('# setCallBackWaitCache #' 
            . ', ['. $tags . '_' . $type .']' 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }
}
