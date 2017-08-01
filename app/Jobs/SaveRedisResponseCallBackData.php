<?php

namespace App\Jobs;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\Models\EasyPayResponseCallBack;
use App\Models\EasyPayWaiting;
use Crypt;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SaveRedisResponseCallBackData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tags;
    private $type;
    private $base_id;
    private $redis_response_call_back;

    public $tries = 3;

    const GETQRCODE = 2;
    const CALLBACK = 3;  

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tags, $type, $base_id, $redis_response_call_back)
    {
        $this->tags = $tags;
        $this->type = $type;
        $this->base_id = $base_id;
        $this->redis_response_call_back = $redis_response_call_back;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        $this->cache_service = $Api500EasyPayCacheService;
 
        $call_back['base_id'] = $this->base_id;
        $call_back['merNo'] = $this->redis_response_call_back['merNo'];
        $call_back['orderNum'] = $this->redis_response_call_back['orderNum'];
        $call_back['goodsName'] = $this->redis_response_call_back['goodsName'];
        $call_back['amount'] = $this->redis_response_call_back['amount'];
        $call_back['netway'] = $this->redis_response_call_back['netway'];
        $call_back['payDate'] = $this->redis_response_call_back['payDate'];
        $call_back['payResult'] = $this->redis_response_call_back['payResult'];
        $call_back['sign'] = Crypt::encrypt($this->redis_response_call_back['sign']);

        Log::info('# redis_response_call_back #'
            . ', base_id = ' . $this->base_id
            . ', redis_response_call_back = ' . print_r($this->redis_response_call_back, true)
            . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__
        );

        $has_call_back = EasyPayResponseCallBack::ofBaseId($call_back['base_id'])->first();

        if (!empty($has_call_back)) {
            Log::info('# call_back data haved #'
                . ', call back data = ' . print_r($has_call_back, true)
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            $this->job->delete();
            return;
        }

        // TODO: 可以新增function處理
        DB::beginTransaction();
        try {
            $insert_call_back = EasyPayResponseCallBack::create($call_back);
            $update_waiting = EasyPayWaiting::ofBaseId($call_back['base_id'])
                ->ofOrderStatus(self::GETQRCODE)
                ->first();
            if (empty($update_waiting)) {
                Log::info('# update_waiting no data #'
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
                );
                DB::rollback();
                return false;    
            }
            $update_waiting->call_back_id = $insert_call_back->id;
            $update_waiting->order_status = self::CALLBACK;
            $update_waiting->save();
            Log::info('# inster & update Mysql success #'
                . ', insert_call_back = ' . print_r($insert_call_back, true)
                . ', update_waiting_data = ' . print_r($update_waiting, true)
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );

            $this->cache_service->deleteCache(
                $this->tags,
                $this->type,
                $call_back['base_id']
            );
            
            DB::commit();
        } catch (QueryException $exception) {    
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
        Log::error('# SaveRedisResponseCallBackData Job fail #' 
            . ', base_id = ' . $this->base_id
            . print_r($this->redis_response_call_back, true) 
            . ', FILE = ' .__FILE__ . 'LINE:' . __LINE__
        );
    }
}
