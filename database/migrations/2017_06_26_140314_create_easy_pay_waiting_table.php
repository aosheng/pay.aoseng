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
            $table->text('sCorpCode')->comment('盤口');
            $table->string('sOrderID', 32)->comment('盤口訂單編號');
            $table->text('iUserKey')->comment('盤口用戶ID');
           
            $table->string('base_id', 32)->comment('only key link cache'); # cache base id

            $table->integer('qrcode_id')
                ->length(10)
                ->unsigned()
                ->nullable()
                ->comment('link respones_get_qrcode'); 

            $table->integer('call_back_id')
                ->length(10)
                ->unsigned()
                ->nullable()
                ->comment('link respones_call_back'); 

            $table->char('order_status', 2)->comment('1:waiting 2:get qrcode 3:call back success 4:fail'); //waiting fail success
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
