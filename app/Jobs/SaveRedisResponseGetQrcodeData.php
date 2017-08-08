<?php

namespace App\Jobs;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\Models\EasyPayResponse;
use App\Models\EasyPayResponseQrcode;
use App\Models\EasyPayWaiting;
use Crypt;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SaveRedisResponseGetQrcodeData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tags;
    private $type;
    private $base_id;
    private $redis_response_get_qrcode;

    public $tries = 3;

    const GETQRCODE = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tags, $type, $base_id, $redis_response_get_qrcode)
    {
        $this->tags = $tags;
        $this->type = $type;
        $this->base_id = $base_id;
        $this->redis_response_get_qrcode = $redis_response_get_qrcode;
    }

    /**
     * Execute the job.
     * Get Redis qrcode data To Mysql
     * @return void
     */
    public function handle(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        $this->cache_service = $Api500EasyPayCacheService;

        $get_qrcode['base_id'] = $this->base_id;
        $get_qrcode['stateCode'] = $this->redis_response_get_qrcode['stateCode'];
        $get_qrcode['msg'] = $this->redis_response_get_qrcode['msg'];
       
        if ($get_qrcode['stateCode'] === '00') {
            $get_qrcode['merNo'] = $this->redis_response_get_qrcode['merNo'];
            $get_qrcode['orderNum'] = $this->redis_response_get_qrcode['orderNum'];
            $get_qrcode['qrcodeUrl'] = $this->redis_response_get_qrcode['qrcodeUrl'];
            $get_qrcode['sign'] = Crypt::encrypt($this->redis_response_get_qrcode['sign']);
        }
        
        Log::info('# redis_response_get_qrcode #'
            . ', base_id = ' . $this->base_id
            . ', redis_response_get_qrcode = ' . print_r($this->redis_response_get_qrcode, true)
            . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__
        );

        $has_qrcode = EasyPayResponseQrcode::where('base_id', $get_qrcode['base_id'])->get();

        if (!$has_qrcode->isEmpty()) {
            Log::info('# qrcode data haved #'
                    . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
                );
            $this->job->delete();
            return;
        }
        // TODO: 可以新增function處理
        DB::beginTransaction();
        try {
            $insert_qrcode_data = EasyPayResponseQrcode::create($get_qrcode);
            $update_waiting = EasyPayWaiting::where('base_id', $get_qrcode['base_id'])->first();
            $update_waiting->qrcode_id = $insert_qrcode_data->id;
            $update_waiting->order_status = self::GETQRCODE;
            $update_waiting->save();

            Log::info('# inster & update Mysql success #'
                . ', insert_qrcode_data = ' . print_r($insert_qrcode_data, true)
                . ', update_waiting_data = ' . print_r($update_waiting, true)
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            
            $this->cache_service->deleteCache(
                $this->tags,
                $this->type,
                $get_qrcode['base_id']
            );
            DB::commit();
        } catch (\QueryException $exception) {
            Log::error('# inster Mysql error #'
                . ', Exception = ' . print_r($exception, true)
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            DB::rollback();
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        // Called when the job is failing...
        Log::error('# SaveRedisResponseGetQrcodeData Job fail #' 
            . ', base_id = ' . $this->base_id
            . print_r($this->redis_response_get_qrcode, true) 
            . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__
        );
    }
}
