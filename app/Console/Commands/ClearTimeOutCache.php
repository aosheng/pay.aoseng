<?php

namespace App\Console\Commands;

use App\Http\Cache\BaseCacheHelper;
use App\jobs\ClearTimeOutRedisCache;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearTimeOutCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Clear:timeout_cache {payment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Time Out Cache {payment}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BaseCacheHelper $BaseCacheHelper)
    {
        parent::__construct();
        $this->base_cache = $BaseCacheHelper;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->tags = $this->argument('payment');

        $clear_list = $this->base_cache->getZaddList($this->tags, 'timestamp', 'WITHSCORES');

        $now = Carbon::now();

        foreach ($clear_list as $base_id => $timestamp) {
            $after_one_day = Carbon::createFromTimeStamp($timestamp)->addDay();
            if ($now->lt($after_one_day)) {
                return false;
            }
            dispatch((new ClearTimeOutRedisCache($this->tags, $base_id))
                ->onQueue('clear_timeout_redis'));
        }    

    }
}
