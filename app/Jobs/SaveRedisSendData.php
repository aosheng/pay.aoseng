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
        //dd($this->redis_send_data);
        Log::info('# SaveRedisSendData #' . print_r($this->redis_send_data, true));
        $redis_send_data['sCorpCode'] = $this->redis_send_data['config']['sCorpCode'];
        $redis_send_data['sOrderID'] = $this->redis_send_data['config']['sOrderID'];
        $redis_send_data['iUserKey'] = $this->redis_send_data['config']['iUserKey'];
        $redis_send_data['payment'] = $this->redis_send_data['config']['pay'];
        $redis_send_data['base_id'] = $this->redis_send_data['base_id'];

        $redis_send_data['config_merNo'] = $this->redis_send_data['merNo'];
        $redis_send_data['config_signKey'] = $this->redis_send_data['signKey'];
        $redis_send_data['config_encKey'] = $this->redis_send_data['encKey'];
        $redis_send_data['config_payUrl'] = $this->redis_send_data['payUrl'];
        $redis_send_data['config_remitUrl'] = $this->redis_send_data['remitUrl'];

        $redis_send_pay_data = json_decode($this->redis_send_data['data']['data']);

        $redis_send_data['version'] = $redis_send_pay_data->version;
        $redis_send_data['merNo'] = $redis_send_pay_data->merNo;
        $redis_send_data['netway'] = $redis_send_pay_data->netway;
        $redis_send_data['random'] = $redis_send_pay_data->random;
        $redis_send_data['orderNum'] = $redis_send_pay_data->orderNum;
        $redis_send_data['goodsName'] = $redis_send_pay_data->goodsName;
        $redis_send_data['callBackUrl'] = $redis_send_pay_data->callBackUrl;
        $redis_send_data['callBackViewUrl'] = $redis_send_pay_data->callBackViewUrl;
        $redis_send_data['charset'] = $redis_send_pay_data->charset;
        $redis_send_data['sign'] = $redis_send_pay_data->sign;
        
        EasyPaySend::create($redis_send_data);

    // [data] => Array
    //     (
    //         [data] => {"amount":"100","callBackUrl":"http://pay.aosheng.com/api/Api500EasyPay/pay_callback","callBackViewUrl":"","charset":"utf-8","goodsName":"测试支付WX","merNo":"QYF201705260107","netway":"WX","orderNum":"201707111054521639","random":"4231","version":"V2.0.0.0","sign":"B8643AF6078A5D10246BD445CF32AD4F"}
    //     )
    }
}
