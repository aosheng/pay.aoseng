<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EasyPayWaiting extends Model
{
    protected $table = "easy_pay_waiting";
    protected $guarded = ['id'];

    public function scopeOfBaseId($query, $base_id)
    {
        return $query->where('base_id', $base_id);
    }

    public function scopeOfOrderStatus($query, $status_code)
    {
        return $query->where('order_status', $status_code);
    }
}
