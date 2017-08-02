<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Cache\BaseCacheHelper;

class ClearTimeOutRedisCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $msagess;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($msagess)
    {
        $this->msagess = $msagess;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(BaseCacheHelper $BaseCacheHelper)
    {
        $this->base_cache = $BaseCacheHelper;
        
        $type = [
            'input_base_id',
            'send',
            'response_get_qrcode',
            'qrcode',
            'wait_call_back',
            'check_call_back',
            'save_call_back'
        ];
        $tags = $this->msagess['tags'];
        $base_id = $this->msagess['base_id'];
        
        array_walk($type, function($value, $key) use($tags, $base_id) {
            $is_delete = $this->base_cache->deleteListValue($tags, $value, $base_id);
            $is_delete_list = $this->base_cache->deleteTagsValue($tags, $value, $base_id);
            $this->base_cache->deleteSaddValue($tags, $value, $base_id);
        });

        $this->base_cache->deleteZaddValue($tags, 'timestamp', $base_id);
    }
}
