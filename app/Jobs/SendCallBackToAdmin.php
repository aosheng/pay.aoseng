<?php

namespace App\Jobs;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendCallBackToAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tags;
    private $type;
    private $call_back_data;
    private $base_id;

    const TYPEWAITCALLBACK = 'wait_call_back';
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tags, $type, $base_id, $call_back_data)
    {
        $this->tags = $tags;
        $this->type = $type;
        $this->base_id = $base_id;
        $this->call_back_data = $call_back_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        $this->cache_service = $Api500EasyPayCacheService;

        $this->cache_service->saveCallBackCache(
            $this->tags,
            $this->type,
            $this->base_id,
            $this->call_back_data
        );
        Log::info('# save_call_back cache success #' 
            . ', tags = ' . $this->tags
            . ', type = ' . $this->type
            . ', base_id = ' . $this->base_id
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        $is_delete = $this->cache_service->deleteListCache(
            $this->tags,
            self::TYPEWAITCALLBACK,
            $this->call_back_data['merNo'] . '_' . $this->call_back_data['orderNum']
        );
        Log::info('# delete list #'
            . ', is_delete = ' . $is_delete
            . ', [' . $this->tags . '_' . self::TYPEWAITCALLBACK .']'
            . ', merNo_ordernum = ' . $this->call_back_data['merNo'] . '_' . $this->call_back_data['orderNum'] 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
        $is_delete_tags = $this->cache_service->deleteTagsCache(
            $this->tags,
            self::TYPEWAITCALLBACK,
            $this->call_back_data['merNo'] . '_' . $this->call_back_data['orderNum']
        );
        Log::info('# forget tags data #'
            . ', is_delete_tags = ' . $is_delete_tags
            . ', [' . $this->tags . '_' . self::TYPEWAITCALLBACK .']' 
            . ', merNo_ordernum = ' . $this->call_back_data['merNo'] . '_' . $this->call_back_data['orderNum'] 
            . ', FILE = '. __FILE__ . 'LINE:' . __LINE__
        );
        
        // TODO: 通知後台更新, 再删除save call back cache, 如cache 已被刪除, 需去資料庫抓取

        // TODO: 刪掉過時的wait call back cache, 可以從waiting table 去抓取 
    }

    public function failed()
    {
        // Called when the job is failing...
        Log::error('# SendCallBackToAdmin Job fail #' 
            . ', base_id = ' . $this->base_id
            . ', call_back_data = ' . print_r($this->call_back_data, true)
            . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__
        );
    }
}
