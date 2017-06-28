<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create500EasyPayWaitingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('500_easy_pay_waiting', function (Blueprint $table) {
            $table->increments('id');
            $table->string('base_id', 32); # cache base id
            $table->string('merNo', 16); 
            $table->string('stateCode', 16);
            $table->string('msg', 4);
            $table->string('orderNum', 20);
            $table->string('qrcodeUrl', 128);
            $table->string('sign', 32);
            $table->string('order_status', 8); //waiting fail success
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
        Schema::drop('500_easy_pay_waiting');
    }
}
