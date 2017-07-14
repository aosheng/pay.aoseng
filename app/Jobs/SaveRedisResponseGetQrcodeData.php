<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Crypt;
use App\Models\EasyPayResponse;
use Log;
use DB;
use Illuminate\Database\QueryException;
use App\Http\Services\Cache\Api500EasyPayCacheService;


class SaveRedisResponseGetQrcodeData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $redis_response_get_qrcode;

    public $tries = 3;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($redis_response_get_qrcode)
    {
        $this->redis_response_get_qrcode = $redis_response_get_qrcode;
        Log::info('redis_response_get_qrcode' . print_r($this->redis_response_get_qrcode, true) . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //$this->get_qrcode = $redis_response_get_qrcode;
        //Log::info('redis_response_get_qrcode' . print_r($this->get_qrcode, true) . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__);
    }
}
