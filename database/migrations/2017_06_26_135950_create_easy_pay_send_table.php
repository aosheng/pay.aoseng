<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEasyPaySendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('easy_pay_send', function (Blueprint $table) {
            $table->increments('id');
            // 盤口資訊
            $table->string('sCorpCode', 32)->comment('盤口');
            $table->string('sOrderID', 32)->comment('盤口訂單編號');
            $table->string('iUserKey', 32)->comment('盤口用戶ID');
            $table->string('payment', 32)->comment('付款類型');

            $table->string('base_id', 32)->comment('only key link cache'); # cache base id
            // config
            $table->string('config_merNo', 16)->comment('商戶號'); #'QYF201705260107';
            $table->string('config_signKey', 32)->comment('MD5密鑰'); #'2566AE677271D6B88B2476BBF923ED88';
            $table->string('config_encKey', 32)->comment('3DES密鑰'); #'GiWBZqsJ4GYZ8G8psuvAsTo3';
            $table->string('config_payUrl', 128)->comment('支付宝或微信地址'); #'http://47.90.116.117:90/api/pay.action';
            $table->string('config_remitUrl', 128)->comment('代付地址'); #'http://47.90.116.117:90/api/remit.action';
            //  detail
            $table->string('version', 8)->comment('版本號'); #'V2.0.0.0';
            $table->string('merNo', 16)->comment('商戶號'); # config merNo
            $table->string('netway', 16)->comment('付款方式'); #'WX';    
            $table->string('random', 4)->comment('隨機碼'); #(string) rand(1000,9999);
            $table->string('orderNum', 20)->comment('訂單編號'); #date('YmdHis') . rand(1000,9999);
            $table->float('amount')->comment('訂單金額（单位：分）'); #'1000 （单位：分） 送去前要*100, 回來記得除100';
            $table->string('goodsName', 20)->comment('商品名稱 (可做為顯示訂單號碼用)'); #'测试支付';
            $table->string('callBackUrl', 128)->comment('接收第三方回資料網址'); #'http://localhost/api/Api500EasyPay/pay_callback';
            $table->string('callBackViewUrl', 128)->comment('暫無作用'); #"";
            $table->string('charset', 10)->comment('系統編碼'); #'utf-8';
            $table->string('sign', 32)->comment('簽名'); #'utf-8';
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
        Schema::drop('easy_pay_send');
    }
}
