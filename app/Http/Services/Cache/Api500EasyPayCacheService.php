<?php
namespace App\Http\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Log;

class Api500EasyPayCacheService
{
    const SURVIVAL_TIME = 20;
    const LIMIT = 20;
    
    public function __construct()
    {

    }

    public function setCache($tags, $data)
    {

        // into redis
        $base_id = $tags . '_' . uniqid();

        Redis::rpush($tags . '_base_id', $base_id);
        Cache::store('redis')
            ->tags([$tags])
            ->add($base_id, $data, self::SURVIVAL_TIME);
        
        Log::info('setCache : '  . '['. $tags . '] : ' 
            . 'base_id = ' . $base_id 
            . 'data = ' . print_r($data, true) 
            . __FILE__ . 'LINE:' . __LINE__);

        return $base_id;
    }

    public function getCache($tags)
    {
        $tasks = Redis::lrange($tags . '_base_id', 0, -1);
        $task_data = [];
        $return_data = [];
        if (!$tasks) {
            Log::info('getCache tasks null : ' . __FILE__ . 'LINE:' . __LINE__);
            return false;
        }

        foreach ($tasks as $task_base_id) {
            $get_task = Cache::store('redis')
                ->tags([$tags])
                ->get($task_base_id);
            
            if (!$get_task) {
                Log::warning('redis get warning :' . '['. $tags . '] : ' . 'base_id = ' . $task_base_id . __FILE__ . 'LINE:' . __LINE__);
                continue;
            }

            $task_data = array_merge($get_task,
                array('base_id' => $task_base_id)
            );
            array_push($return_data, $task_data);
        }

        Log::info('getCache_data : ' . print_r($return_data, true) . __FILE__ . 'LINE:' . __LINE__);

        return $return_data;
    }

    public function setSendCache($tags, $type, $base_id, $data)
    {
        
        Redis::rpush($tags . '_' . $type, $base_id);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->add($base_id, $data, self::SURVIVAL_TIME);
        
        Log::info('setSendCache : ['. $tags . '_' . $type .'],
            base_id = '. $base_id . ',
            data = ' . print_r($data, true) . __FILE__ . 'LINE:' . __LINE__);
    }
    
    public function setResponseCache($tags, $type, $base_id, $data)
    {
        
        Redis::rpush($tags . '_' . $type, $base_id);
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->add($base_id, $data, self::SURVIVAL_TIME);
        
        Log::info('setResponseCache : ['. $tags . '_' . $type .'],
            base_id = '. $base_id . ',
            data = ' . print_r($data, true) . __FILE__ . 'LINE:' . __LINE__);

        self::setCallBackWaitCache($tags, 'call_back_wait', $base_id, $data);
        Log::info('setCallBackWaitCache : ['. $tags . '_call_back_wait]' . __FILE__ . 'LINE:' . __LINE__);

    }

    public function setCallBackWaitCache($tags, $type, $base_id, $data)
    {
        // $data['merNO']
        // $data['orderNum']
        if (!$data) {
            return false;
        }
        Redis::rpush($tags . '_' . $type, $data['merNo'] . '_' . $data['orderNum']);
        
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->add($data['merNo'] . '_' . $data['orderNum'], $base_id, 180);


        // 取call back 資料
        Log::info(Cache::store('redis')->tags([$tags . '_' . $type])->get($data['merNo'] . '_' . $data['orderNum']));
        // 取send的資料 用來取sing key
        Log::info('--->' . print_r(Cache::store('redis')->tags(['Api500EasyPay_send'])->get('Api500EasyPay_input_59536120bc1cf'), true));
    }

    public function saveCallBackCache($tags, $type, $base_id, $data)
    {
        //dd([$tags, $type, $base_id, $data]);
        Redis::rpush($tags . '_' . $type, $data['merNo'] . '_' . $data['orderNum']);
        
        Cache::store('redis')
            ->tags([$tags . '_' . $type])
            ->forever($data['merNo'] . '_' . $data['orderNum'], ['base_id' => $base_id, 'data' => $data]);
        Log::info('save call_back success : ['
            . $tags . '_' . $type . '] :'
            . $data['merNo'] . '_' . $data['orderNum']
            . 'base_id' . $base_id
            . __FILE__ . 'LINE:' . __LINE__);
    }

    public function getCallBackWaitCache($tags, $type, $merNo, $orderNum)
    {
        // 取call back 資料
        //dd([$tags, $type, $merNo, $orderNum]);
        return Cache::store('redis')->tags([$tags . '_' . $type])->get($merNo . '_' . $orderNum);
    }

    public function checkCallBackCache($tags, $type, $merNo, $orderNum)
    {
        return Cache::store('redis')->tags([$tags . '_' . $type])->get($merNo . '_' . $orderNum);
    }

    public function getSendCache($tags, $type, $base_id)
    {
        return Cache::store('redis')->tags([$tags . '_' . $type])->get($base_id);
    }

    public function toGetResponseQrcode($tags, $type, $base_id)
    {
        return Cache::store('redis')
                ->tags([$tags . '_' . $type])
                ->get($base_id);
    }

    public function deleteCache($tags, $type, $base_id)
    {
        Redis::lpop($tags . '_' . $type);
        
        Log::info('delete list [' . $tags . '_' . $type .'] base_id= ' . $base_id . __FILE__ . 'LINE:' . __LINE__);
    }

    public function deleteTagsCache($tags, $type, $base_id)
    {
        Cache::store('redis')
            ->tags([$tags. '_' . $type])
            ->forget($base_id);
        
        Log::info('forget [' . $tags . '_' . $type .'] base_id= ' . $base_id . __FILE__ . 'LINE:' . __LINE__);
    }
}
