<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create500EasyPayResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('500_easy_pay_response', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merNo', 16); 
            $table->string('netway', 16); 
            $table->string('orderNum', 20); 
            $table->float('amount', 14);
            $table->string('goodsName', 20); 
            $table->string('payResult', 16); 
            $table->dateTime('payDate', 19); 
            $table->string('sign', 32); 
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
