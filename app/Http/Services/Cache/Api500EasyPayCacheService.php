<?php
namespace App\Http\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;

class Api500EasyPayCacheService
{
    const SURVIVAL_TIME = 20;
    const LIMIT = 50;
    
    public function __construct()
    {
        Cache::store('redis')->setPrefix('500EasyPay');
    }

    public function setCache($tags, $type, $data)
    {
        $base_id = $tags . '_' . uniqid();
        
        Redis::rpush($tags . '_' . $type, $base_id);
        Cache::store('redis')
            ->tags([$tags])
            ->add($base_id, $data, self::SURVIVAL_TIME);
        
        self::setMerNoOrdeNum($tags, $type, $data, $base_id);
        
        Log::info('# setCache #'  
            . ', ['. $tags . ']' 
            . ', base_id = ' . $base_id 
            . ', data = ' . print_r($data, true) 
            . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__
        );

        return $base_id;
    }

    public function setMerNoOrdeNum($tags, $type, $data, $base_id)
    {
        //var_dump(json_decode($data['data']['data']));
        $order_data = json_decode($data['data']['data']);
        // 設置 base_id 的 商號 + 訂單 對照
        Redis::set(
            $tags . '_' . $type . '_' . $data['config']['merNo'] . '_' .  $order_data->orderNum,
            $base_id
        );
    }

    public function getCache($tags, $type)
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
    
    public function setResponseCache($tags, $type, $base_id, $data)
    {   
        Redis::rpush($tags . '_' . $type, $base_id);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->add($base_id, $data, self::SURVIVAL_TIME);
        
        Log::info('# setResponseCache #'
            . ', ['. $tags . '_' . $type .']'
            . ', base_id = '. $base_id 
            . ', data = ' . print_r($data, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        self::setCallBackWaitCache($tags, 'call_back_wait', $base_id, $data);
        Log::info('# setCallBackWaitCache #' 
            . ', ['. $tags . '_call_back_wait]' 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

    }

    public function setCallBackWaitCache($tags, $type, $base_id, $data)
    {
        if (!$data) {
            return false;
        }
        Redis::rpush($tags . '_' . $type, $data['merNo'] . '_' . $data['orderNum']);
        
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->add($data['merNo'] . '_' . $data['orderNum'], $base_id, self::SURVIVAL_TIME);
        }

    public function saveCallBackCache($tags, $type, $base_id, $data)
    {
        Log::info('# start call_back #'
            . ', [' . $tags . '_' . $type . ']'
            . ', ' .$data['merNo'] . '_' . $data['orderNum']
            . ', base_id = ' . $base_id
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
        Redis::rpush($tags . '_' . $type, $data['merNo'] . '_' . $data['orderNum']);
        
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($data['merNo'] . '_' . $data['orderNum'], ['base_id' => $base_id, 'data' => $data]);
    }

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

    public function hasQrcode($tags, $type, $merNo, $orderNum)
    {
        return Redis::Get($tags . '_' . $type . '_' . $merNo . '_' . $orderNum);
    }

    public function toGetResponseQrcode($tags, $type, $base_id)
    {
        return Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->get($base_id);
    }

    public function deleteListCache($tags, $type, $base_id)
    {
        //Redis::lpop($tags . '_' . $type);
        $is_delete = Redis::LREM($tags . '_' . $type, 0, $base_id); 
        Log::info('# delete list #'
            . ', is_delete = ' . $is_delete
            . ', [' . $tags . '_' . $type .']'
            . ', base_id = ' . $base_id 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }

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
}
