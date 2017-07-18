<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EasyPayResponseCallBack extends Model
{
    protected $table = "easy_pay_response_call_back";
    protected $guarded = ['id'];

    public function scopeOfBaseId($base_id)
    {
        return self::where('base_id', $base_id);
    }
}
