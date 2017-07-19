<?php

namespace App\Console\Commands;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\jobs\SaveRedisResponseCallBackData;
use App\jobs\SaveRedisResponseGetQrcodeData;
use App\jobs\SaveRedisSendData;
use Illuminate\Console\Command;

class GetRedisData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Redis_Action:GetData {payment} {action} {other?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Redis Data : --option {payment} {action} {other?}';

    protected $Api500EasyPayCacheService;
    protected $tags;
    protected $action;
    protected $other;

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
        $this->other = ($this->argument('other')) ? $this->argument('other') : null;

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

        if ($this->action == 'response' && $this->other == 'get_qrcode') {
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
            foreach ($response_get_qrcode_list as $base_id) {
                $response_get_qrcode_data = $this->cache_service->getResponseQrcode($this->tags, $this->action . '_' . $this->other, $base_id);
                
                if ($response_get_qrcode_data) {
                    dispatch((new SaveRedisResponseGetQrcodeData($base_id, $response_get_qrcode_data))
                        ->onQueue('get_redis_insert_mysql'));
                }
            }
        }

        if ($this->action == 'save' && $this->other == 'call_back') {
            $this->other = $this->argument('other');
            $response_call_back_list = $this->cache_service->getSaveCallBackList(
                $this->tags,
                $this->action . '_' . $this->other
            );
            
            if (empty($response_call_back_list)) {
                $this->info('Can not find '
                    . $this->argument('payment')
                    . '_' . $this->action
                    . '_' . $this->other
                    . ' cache'
                );
                return;
            }
            foreach ($response_call_back_list as $base_id) {
                $response_call_back_data = $this->cache_service->getSaveCallBack($this->tags, $this->action . '_' . $this->other, $base_id);
                
                if ($response_call_back_data) {
                    dispatch((new SaveRedisResponseCallBackData($base_id, $response_call_back_data))
                        ->onQueue('get_redis_insert_mysql'));
                }
            }
        }

        echo date("Y-m-d H:i:s")."\n";
    }
}
