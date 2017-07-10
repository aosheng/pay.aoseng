<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEasyPayResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('easy_pay_response', function (Blueprint $table) {
            $table->increments('id');
         
            $table->string('base_id', 32)->comment('only key link cache'); # cache base id
            $table->string('merNo', 16)->comment('商戶號'); 
            $table->string('netway', 16)->comment('付款方式'); 
            $table->string('orderNum', 20)->comment('訂單編號'); 
            $table->float('amount', 14)->comment('訂單金額（单位：分）');
            $table->string('goodsName', 20)->comment('商品名稱 (可做為顯示訂單號碼用)'); 
            $table->string('payResult', 16)->comment('支付狀態 00表示成功'); 
            $table->dateTime('payDate', 19)->comment('支付時間 yyyyMMddHHmmss');
            $table->string('sign', 32)->comment('簽名'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('500_easy_pay_response');
    }
}
