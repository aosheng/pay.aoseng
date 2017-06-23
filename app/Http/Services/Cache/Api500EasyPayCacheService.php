<?php
namespace App\Http\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

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
        // var_dump($base_id);
        // var_dump($tags);
        //dd($tags . '_base_id');
        //array_push($data, ['base_id' => $base_id]);
        Redis::rpush($tags . '_base_id', $base_id);    
        Cache::store('redis')->tags([$tags])->add($base_id, $data, 20);

        //var_dump(Redis::lrange($tags . '_base_id', 0, 50));

        //self::getCache($tags)

    }

    public function getCache($tags)
    {
        $tasks = Redis::lrange($tags . '_base_id', 0, 30);
        $task_data = [];
        $return_data = [];
        //dd($tasks);
        //dd(Cache::tags([$tags])->get('input_Api500EasyPay_594b37b3c2db6'));
        foreach ($tasks as $task_base_id) {
            
            // 进 queue 成功再删掉
            // if (true) {
            //     Redis::lpop('base_id');
            //     Cache::tags(['input_Api500EasyPay'])->forget($task_base_id);
            // }

            var_dump(Cache::store('redis')->tags([$tags])->get($task_base_id));
            
            $task_data = array_merge(
                    Cache::store('redis')->tags([$tags])->get($task_base_id),
                        array('base_id' => $task_base_id)
                    
                );
            
            if(!is_null($task_data)) {
                // array_push($task_data, 
                //     array_merge($task_data,
                //         array('base_id' => $task_base_id)
                //     )
                // );
                array_push($return_data, $task_data);
            }
           
        }
        //dd($return_data);
        return $return_data;
    }

    public function setResponseCache($tags, $type, $base_id, $data)
    {
        
        Redis::rpush($tags . '_' . $type, $base_id);    
        Cache::store('redis')->tags([$tags . '_' . $type])->add($base_id, $data, 20);

        //var_dump(Redis::lrange($tags . '_base_id', 0, 50));

        //self::getCache($tags)

    }

    public function deleteCache($tags, $type, $base_id)
    {
        Redis::lpop($tags . '_' . $type);
       
    }

    public function deleteTagsCache($tags, $type, $base_id)
    {
        Cache::store('redis')->tags([$tags. '_' . $type])->forget($base_id);
    }
}