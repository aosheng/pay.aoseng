<?php
namespace App\Http\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;

class Api500EasyPayCacheService
{
    const SURVIVAL_TIME = 1440;
    const SENDLISTLIMIT = 200;
    
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
            ->tags([$tags])
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
     * @return [array]
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
                ->tags([$tags])
                ->get($task_base_id);
            
            if (!$get_task) {
                Log::warning('# get cache warning #' 
                    . ', ['. $tags . ']' 
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

    public function setSendCache($tags, $type, $base_id, $data)
    {
        Redis::rpush($tags . '_' . $type, $base_id);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->add($base_id, $data, self::SURVIVAL_TIME);
        
        Log::info('# setSendCache success #'
            . ', ['. $tags . '_' . $type .']'
            . ', base_id = '. $base_id 
            . ', data = ' . print_r($data, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }

    public function getSendListCache($tags, $type)
    {
        return Redis::lrange($tags . '_' . $type, 0, self::SENDLISTLIMIT);
    }
    
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
                'wait_call_back',
                $base_id,
                $data
            );
        }

        Log::info('# setCallBackWaitCache #' 
            . ', ['. $tags . '_wait_call_back]' 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }

    public function saveCallBackCache($tags, $type, $base_id, $data)
    {
        Log::info('# start call_back #'
            . ', [' . $tags . '_' . $type . ']'
            . ', data = ' . print_r($data, true)
            . ', base_id = ' . $base_id
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
        Redis::rpush($tags . '_' . $type, $base_id);
        
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($base_id, $data);
    }

    public function getSaveCallBackList($tags, $type)
    {
        return Redis::lrange($tags . '_' . $type, 0, self::SENDLISTLIMIT);
    }

    public function getSaveCallBack($tags, $type, $base_id)
    {
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($base_id);
    }

    /**
     * Wait Call Back function
     *
     * @param [Str] $tags
     * @param [Str] $type
     * @param [Str] $merNo
     * @param [Num] $orderNum
     * @return base_id
     */
    public function getCallBackWaitCache($tags, $type, $merNo, $orderNum)
    {
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($merNo . '_' . $orderNum);
    }

    public function checkCallBackCache($tags, $type, $merNo, $orderNum)
    {
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($merNo . '_' . $orderNum);
    }

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

    public function getResponseQrcodeList($tags, $type)
    {
        return Redis::lrange($tags . '_' . $type, 0, self::SENDLISTLIMIT);
    }
    /**
     * 回傳的Qrcode
     * @param [String] $tags 支付別名
     * @param [String] $type 組別(response_get_qrcode)
     * @param [String] $base_id 唯一key
     * @return [json]
     */
    public function getResponseQrcode($tags, $type, $base_id)
    {
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($base_id);
    }
    // TODO exception    
    public function deleteListCache($tags, $type, $base_id)
    {
        $is_delete = Redis::LREM($tags . '_' . $type, 0, $base_id);
        Log::info('# delete list #'
            . ', is_delete = ' . $is_delete
            . ', [' . $tags . '_' . $type .']'
            . ', base_id = ' . $base_id 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }
    // TODO exception
    public function deleteTagsCache($tags, $type, $base_id)
    {
        if (!$type) {
            $is_delete_tags = Cache::store('redis')
                ->tags([$tags])
                ->forget($base_id);
        } else {
            $is_delete_tags = Cache::store('redis')
                ->tags([$tags. '_' . $type])
                ->forget($base_id);
        }
        
        Log::info('# forget tags key#'
            . ', is_delete_tags = ' . $is_delete_tags
            . ', [' . $tags . '_' . $type .']' 
            . ', base_id = ' . $base_id 
            . ', FILE = '. __FILE__ . 'LINE:' . __LINE__
        );
    }

    private function setCallBackWaitCache($tags, $type, $base_id, $data)
    {
        Redis::rpush($tags . '_' . $type, $data['merNo'] . '_' . $data['orderNum']);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($data['merNo'] . '_' . $data['orderNum'], $base_id);
    }
}
