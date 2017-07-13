<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Crypt;
use App\Models\EasyPaySend;
use Log;

class SaveRedisSendData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $redis_send_data;

    public $tries = 3;

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
    public function handle()
    {
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
        try {
            $easy_pay_data = EasyPaySend::create($redis_send_data);
            Log::info('# inster Mysql success #' 
                . ', easy_pay_data = ' . print_r($easy_pay_data, true)
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
            // TODO:: 删除cache
        } catch (\Exception $exception) {
            Log::error('# inster Mysql error #' 
                . ', exception = ' . print_r($exception, true)
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            );
        }
    }
}
