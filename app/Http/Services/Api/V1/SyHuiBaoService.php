<?php
namespace App\Http\Services\Api\V1;

use Cache;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Redis;
//use App\Http\Services\Cache\Api500EasyPayCacheService;
use Log;
use App\jobs\SendCallBackToAdmin;

class Api500EasyPayService
{
    public function send($params)
    {
        $params = json_decode($params);
    }
}