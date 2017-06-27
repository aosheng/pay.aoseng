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
            'merNo' #'QYF201705260107';
            'signKey' #'2566AE677271D6B88B2476BBF923ED88';
            'encKey' #'GiWBZqsJ4GYZ8G8psuvAsTo3';
            'payUrl' #'http://47.90.116.117:90/api/pay.action';
            'remitUrl' #'http://47.90.116.117:90/api/remit.action';

            'version' #'V2.0.0.0';
            'merNo' # config merNo
            'netway' #'WX';    
            'random' #(string) rand(1000,9999);
            'orderNum' #date('YmdHis') . rand(1000,9999);
            'amount' #'1000';
            'goodsName' #'测试支付';
            'charset' #'utf-8';
            'callBackUrl' #'http://localhost/api/Api500EasyPay/pay_callback';
            'callBackViewUrl' #"";
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
