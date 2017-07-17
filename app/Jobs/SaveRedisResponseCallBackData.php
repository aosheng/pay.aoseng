<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\EasyPayWaiting;
use Crypt;
use DB;
use Log;
use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\Models\EasyPayResponseCallBack;

class SaveRedisResponseCallBackData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $base_id;
    protected $redis_response_get_qrcode;

    public $tries = 3;

    const GETQRCODE = 2;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    // TODO: 把確實有回應的更新,  篩除 save call back cache
    public function handle()
    {
        //
    }
}
