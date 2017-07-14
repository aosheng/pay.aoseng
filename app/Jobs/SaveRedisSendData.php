<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Crypt;
use App\Models\EasyPaySend;
use App\Models\EasyPayWaiting;
use Log;
use DB;
use Illuminate\Database\QueryException;
use App\Http\Services\Cache\Api500EasyPayCacheService;

class SaveRedisSendData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $redis_send_data;

    public $tries = 3;

    const WAITING = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($redis_send_data)
    {
        $this->redis_send_data = $redis_send_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        $this->cache_service = $Api500EasyPayCacheService;
        Log::info('# SaveRedisSendData #' . print_r($this->redis_send_data, true));
        // 加密栏位 sCorpCode iUserKey signKey encKey sign
        $redis_send_data['sCorpCode'] = Crypt::encrypt($this->redis_send_data['config']['sCorpCode']);
        $redis_send_data['sOrderID'] =  $this->redis_send_data['config']['sOrderID'];
        $redis_send_data['iUserKey'] =  Crypt::encrypt($this->redis_send_data['config']['iUserKey']);
        $redis_send_data['payment'] =   $this->redis_send_data['config']['payment'];
        $redis_send_data['base_id'] =   $this->redis_send_data['base_id'];

        $redis_send_data['config_merNo'] = $this->redis_send_data['config']['merNo'];
        $redis_send_data['config_signKey'] = Crypt::encrypt($this->redis_send_data['config']['signKey']);
        $redis_send_data['config_encKey'] = Crypt::encrypt($this->redis_send_data['config']['encKey']);
        $redis_send_data['config_payUrl'] = $this->redis_send_data['config']['payUrl'];
        $redis_send_data['config_remitUrl'] = $this->redis_send_data['config']['remitUrl'];

        $redis_send_pay_data = json_decode($this->redis_send_data['data']['data']);

        $redis_send_data['version'] = $redis_send_pay_data->version;
        $redis_send_data['merNo'] = $redis_send_pay_data->merNo;
        $redis_send_data['netway'] = $redis_send_pay_data->netway;
        $redis_send_data['random'] = $redis_send_pay_data->random;
        $redis_send_data['orderNum'] = $redis_send_pay_data->orderNum;
        $redis_send_data['amount'] = $redis_send_pay_data->amount;
        $redis_send_data['goodsName'] = $redis_send_pay_data->goodsName;
        $redis_send_data['callBackUrl'] = $redis_send_pay_data->callBackUrl;
        $redis_send_data['callBackViewUrl'] = $redis_send_pay_data->callBackViewUrl;
        $redis_send_data['charset'] = $redis_send_pay_data->charset;
        $redis_send_data['sign'] = Crypt::encrypt($redis_send_pay_data->sign);
        Log::info('# EasyPaySend #' . print_r($redis_send_data, true));

        $redis_waiting_data['sCorpCode'] = $redis_send_data['sCorpCode'];
        $redis_waiting_data['sOrderID'] = $redis_send_data['sOrderID'];
        $redis_waiting_data['iUserKey'] = $redis_send_data['iUserKey'];
        $redis_waiting_data['base_id'] = $redis_send_data['base_id'];
        $redis_waiting_data['order_status'] = self::WAITING;
        Log::info('# EasyPayWaiting #' . print_r($redis_waiting_data, true));
        
        $has_easy_pay = EasyPaySend::where('base_id', $redis_send_data['base_id'])->get();
        if (!$has_easy_pay->isEmpty()) {
            Log::info('# base_id data haved #'
                        . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
                );
            $this->job->delete();
            return;
        }  

        DB::beginTransaction();
        try {
            $insert_send_data = EasyPaySend::create($redis_send_data);
            $insert_waiting_data = EasyPayWaiting::create($redis_waiting_data);
            DB::commit();
            Log::info('# inster Mysql success #'
                . ', insert_send_data = ' . print_r($insert_send_data, true)
                . ', insert_waiting_data = ' . print_r($insert_waiting_data, true)
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
                );
            // TODO:: 删除cache
            $this->cache_service->deleteListCache('Api500EasyPay', 'send', $redis_send_data['base_id']);
            $this->cache_service->deleteTagsCache('Api500EasyPay', 'send', $redis_send_data['base_id']);
            
        } catch (QueryException $exception) {
            DB::rollback();
            Log::error('# inster Mysql error #'
                . ', Exception = ' . print_r($exception, true)
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
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
        Log::error('# SaveRedisSendData Job fail #'
            . ', redis_send_data = ' . print_r($this->redis_send_data, true)
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }
}
