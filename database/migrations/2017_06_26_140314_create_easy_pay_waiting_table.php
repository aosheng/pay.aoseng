<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEasyPayWaitingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('easy_pay_waiting', function (Blueprint $table) {
            $table->increments('id');

            // 盤口資訊
            $table->string('sCorpCode', 32)->comment('盤口');
            $table->string('sOrderID', 32)->comment('盤口訂單編號');
            $table->string('iUserKey', 32)->comment('盤口用戶ID');

            $table->string('base_id', 32)->comment('only key link cache'); # cache base id
            $table->string('merNo', 16)->comment('商戶號'); 
            $table->string('stateCode', 16)->comment('get狀態'); 
            $table->string('msg', 4)->comment('get訊息'); 
            $table->string('orderNum', 20)->comment('訂單編號');
            $table->string('qrcodeUrl', 128)->comment('qrcode');
            $table->string('sign', 32)->comment('簽名');
            $table->string('order_status', 8)->comment('1:waiting 2:success 3:fail'); //waiting fail success
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
        Schema::drop('easy_pay_waiting');
    }
}
