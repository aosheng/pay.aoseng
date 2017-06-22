<?php
namespace App\Transformers;

use App\Models\Api500EasyPay;
use League\Fractal\TransformerAbstract;

class Api500EasyPayTransformer extends TransformerAbstract
{
    public function transform(Api500EasyPay $Api500EasyPay)
    {
        return $Api500EasyPay->attributesToArray();
    }
}