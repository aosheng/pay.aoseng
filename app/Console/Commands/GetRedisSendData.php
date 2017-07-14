<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\jobs\SaveRedisSendData;
use App\jobs\SaveRedisResponseGetQrcodeData;

class GetRedisSendData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Redis_Action:GetSendData {payment} {action} {other?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Redis Third Pay Data : --option {payment} {action} {other?}';

    protected $Api500EasyPayCacheService;
    protected $tags;
    protected $action;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        parent::__construct();
        $this->cache_service = $Api500EasyPayCacheService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    // TODO: cache需调整成动态调配
    public function handle()
    {   
        $this->tags = $this->argument('payment');
        $this->action = $this->argument('action');

        if ($this->action == 'send') {
            $send_list = $this->cache_service->getSendListCache($this->tags, $this->action);
            if (empty($send_list)) {
                $this->info('Can not find ' 
                    . $this->argument('payment') 
                    . '_' . $this->action 
                    . ' cache'
                );
                return;
            }
            foreach ($send_list as $base_id) {
                $redis_send_data = $this->cache_service->getSendCache($this->tags, $this->action, $base_id);
                
                dispatch((new SaveRedisSendData($redis_send_data))
                    ->onQueue('get_redis_insert_mysql'));
            }
        }

        if ($this->action == 'response') {
            $this->other = $this->argument('other');
            $response_get_qrcode_list = $this->cache_service->getResponseQrcodeList(
                    $this->tags,
                    $this->action . '_' . $this->other
                );
            
            if (empty($response_get_qrcode_list)) {
                $this->info('Can not find ' 
                    . $this->argument('payment') 
                    . '_' . $this->action 
                    . '_' . $this->other 
                    . ' cache'
                );
                return;
            }
//dd($response_get_qrcode_list);
            foreach ($response_get_qrcode_list as $base_id) {
                ///dd($this->cache_service->getResponseQrcode($this->tags, $this->action . '_' . $this->other, $base_id));       
                $response_get_qrcode_data = $this->cache_service->getResponseQrcode($this->tags, $this->action . '_' . $this->other, $base_id);
                //dd($response_get_qrcode_data);
                if ($response_get_qrcode_data) {
                   
                    dispatch((new SaveRedisResponseGetQrcodeData($response_get_qrcode_data))
                        ->onQueue('get_redis_insert_mysql'));
                }
            }
        }


        echo date("Y-m-d H:i:s")."\n";
    }
}
