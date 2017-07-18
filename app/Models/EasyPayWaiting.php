<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EasyPayWaiting extends Model
{
    protected $table = "easy_pay_waiting";
    protected $guarded = ['id'];

    public function scopeOfBaseId($base_id)
    {
        return self::where('base_id', $base_id);
    }

    public function scopeOfOrderStatus($status_code)
    {
        return self::where('order_status', $status_code);
    }
}
