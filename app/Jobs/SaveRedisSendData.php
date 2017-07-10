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
        Log::info('SaveRedisSendData' . print_r($this->redis_send_data, true));
        //EasyPaySend::create();
        
    }
}
