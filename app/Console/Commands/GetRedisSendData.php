<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\jobs\SaveRedisSendData;

class GetRedisSendData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Redis_Action:GetSendData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Redis Send Third Pay Data';

    protected $Api500EasyPayCacheService;
    protected $tags;
    protected $type;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        parent::__construct();
        $this->cache_service = $Api500EasyPayCacheService;
        $this->tags = 'Api500EasyPay';
        $this->type = 'send';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $send_list = $this->cache_service->getSendListCache($this->tags, $this->type);
        
        foreach ($send_list as $base_id) {
            $redis_send_data = $this->cache_service->getSendCache($this->tags, $this->type, $base_id);
            
            dispatch((new SaveRedisSendData($redis_send_data))
                ->onQueue('get_redis_send_data'));
            dd($redis_send_data);
        }
        echo date("Y-m-d H:i:s")."\n";
    }
}
