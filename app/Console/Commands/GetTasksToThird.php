<?php

namespace App\Console\Commands;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\Jobs\GetTasksToThird as GetQrcode;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class GetTasksToThird extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Tothird:GetTasksToThird {payment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send data to Third get Qrcode --option {payment}';

    protected $Api500EasyPayCacheService;
    protected $tags;
    
    const TYPEINPUTBASEID = 'input_base_id';
    const TYPESEND = 'send';

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
    public function handle()
    {
        $this->tags = $this->argument('payment');

        $task_data = $this->cache_service->getInputListData($this->tags, self::TYPEINPUTBASEID);
       
        // Log::info('# Tothird:GetTasksToThird start #' 
        //     . ', task_data = ' . print_r($task_data, true)
        //     . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        // );

        if (empty($task_data)) {
            // Log::warning('# Tothird:GetTasksToThird warning # No data'
            //     . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
            // );
            return false;
        }

        Log::info('# get input redis order # start getQrcode job ' 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );

        foreach ($task_data as $data) {
            dispatch((new getQrcode($this->tags, self::TYPESEND, $data))
                ->onQueue('get_qrcode'));
        }
        echo date("Y-m-d H:i:s")."\n";
    }
}
