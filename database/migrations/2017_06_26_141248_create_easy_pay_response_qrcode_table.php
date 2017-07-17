<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEasyPayResponseQrcodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('easy_pay_response_qrcode', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('base_id', 32)->comment('only key link cache'); # cache base id
            
            $table->string('merNo', 16)->comment('商戶號'); 
            $table->string('stateCode', 16)->comment('get狀態'); 
            $table->string('msg', 4)->comment('get訊息'); 
            $table->string('orderNum', 20)->comment('訂單編號');
            $table->string('qrcodeUrl', 128)->comment('qrcode');
            $table->text('sign')->comment('簽名');
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
        Schema::drop('easy_pay_response_qrcode');
    }
}
