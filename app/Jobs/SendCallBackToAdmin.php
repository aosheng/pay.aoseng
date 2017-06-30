<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use App\Http\Services\Cache\Api500EasyPayCacheService;

class SendCallBackToAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   // protected $Api500EasyPayService;
    protected $Api500EasyPayCacheService;
    protected $call_back_data;
    protected $base_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($base_id, $call_back_data)
    {
        //dd([$base_id, $call_back_data]);
        
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
        //$this->service = $Api500EasyPayService;

        // 確認是否已發過
        $check_call_back = $this->cache_service->checkCallBackCache('Api500EasyPay', 'save_call_back', $this->call_back_data['merNo'], $this->call_back_data['orderNum']);
        
        if ($check_call_back) {
            Log::warning('# call_back saved #'
                . ', [Api500EasyPay_save_call_back]'
                . ', merNo : ' . $this->call_back_data['merNo']
                . ', orderNum : ' . $this->call_back_data['orderNum']
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            return false;
        }

        $this->cache_service->saveCallBackCache('Api500EasyPay', 'save_call_back', $this->base_id, $this->call_back_data);
        Log::info('save_call_back cache success: ' 
            . ', base_id = ' . $this->base_id
            . ', call_back_data = ' . print_r($this->call_back_data, true)
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
        // TODO: 刪掉 setCallBackWaitCache 寫入的key

        // TODO: 通知後台更新
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
