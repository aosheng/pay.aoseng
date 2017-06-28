<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create500EasyPaySendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('500_easy_pay_send', function (Blueprint $table) {
            $table->increments('id');
            $table->string('base_id', 32); # cache base id

            $table->string('merNo', 16); #'QYF201705260107';
            $table->string('signKey', 32); #'2566AE677271D6B88B2476BBF923ED88';
            $table->string('encKey', 32); #'GiWBZqsJ4GYZ8G8psuvAsTo3';
            $table->string('payUrl', 128); #'http://47.90.116.117:90/api/pay.action';
            $table->string('remitUrl', 128); #'http://47.90.116.117:90/api/remit.action';

            $table->string('version', 8); #'V2.0.0.0';
            $table->string('merNo', 16); # config merNo
            $table->string('netway', 16); #'WX';    
            $table->string('random', 4); #(string) rand(1000,9999);
            $table->string('orderNum', 20); #date('YmdHis') . rand(1000,9999);
            $table->float('amount'); #'1000 （单位：分） 送去前要*100, 回來記得除100';
            $table->string('goodsName', 20); #'测试支付';
            $table->string('callBackUrl', 128); #'http://localhost/api/Api500EasyPay/pay_callback';
            $table->string('callBackViewUrl', 128); #"";
            $table->string('charset', 10); #'utf-8';
            $table->string('sign', 32); #'utf-8';
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
        Schema::drop('500_easy_pay_send');
    }
}
