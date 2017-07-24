define({ "api": [
  {
    "type": "POST",
    "url": "/api/Api500EasyPay/store",
    "title": "/api/Api500EasyPay/store",
    "name": "500EasyPay",
    "group": "500EasyPay",
    "version": "1.0.0",
    "description": "<p>500輕易付 發送訂單</p>",
    "permission": [
      {
        "name": "POST"
      }
    ],
    "sampleRequest": [
      {
        "url": "http://testpayaosheng.azurewebsites.net/api/Api500EasyPay/store"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "json",
            "optional": false,
            "field": "config",
            "description": "<p>商家基本設定</p>"
          },
          {
            "group": "Parameter",
            "type": "json",
            "optional": false,
            "field": "pay",
            "description": "<p>訂單資訊</p>"
          }
        ],
        "config": [
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "sCorpCode",
            "description": "<p>盤口號</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "sOrderID",
            "description": "<p>訂單編號</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "iUserKey",
            "description": "<p>用戶ID</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": true,
            "field": "payment",
            "defaultValue": "pay",
            "description": "<p>支付類型</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "payment_service",
            "description": "<p>支付商別名  ex: 500EasyPay</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "merNo",
            "description": "<p>商戶號</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "signKey",
            "description": "<p>MD5密鑰</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "encKey",
            "description": "<p>3DES密鑰</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "payUrl",
            "description": "<p>支付地址</p>"
          },
          {
            "group": "config",
            "type": "string",
            "optional": false,
            "field": "remitUrl",
            "description": "<p>代付地址</p>"
          }
        ],
        "pay": [
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "version",
            "defaultValue": "V2.0.0.0",
            "description": "<p>版本號</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "merNo",
            "description": "<p>商戶號</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": true,
            "field": "netway",
            "description": "<p>WX(微信) 或者 ZFB(支付寶)</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "random",
            "description": "<p>4位隨機數 必須是文本型 ex: (string) rand(1000,9999)</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "orderNum",
            "description": "<p>商户訂單號 ex: date('YmdHis') . rand(1000,9999)</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "amount",
            "description": "<p>默認分為單位 轉换成元需要 * 100   必需是文本型</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "goodsName",
            "description": "<p>商品名稱</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "charset",
            "defaultValue": "utf-8",
            "description": "<p>系统編碼</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "callBackUrl",
            "description": "<p>通知地址</p>"
          },
          {
            "group": "pay",
            "type": "string",
            "optional": false,
            "field": "callBackViewUrl",
            "description": "<p>暫時沒用</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/Api/V1/Api500EasyPayController.php",
    "groupTitle": "500EasyPay",
    "success": {
      "fields": {
        "Request templte": [
          {
            "group": "Request templte",
            "type": "json",
            "optional": false,
            "field": "config",
            "description": "<p>商家基本設定</p>"
          },
          {
            "group": "Request templte",
            "type": "json",
            "optional": false,
            "field": "pay",
            "description": "<p>商家訂單資訊</p>"
          }
        ],
        "Reponse 200": [
          {
            "group": "Reponse 200",
            "type": "number",
            "optional": false,
            "field": "status_code",
            "description": "<p>200</p>"
          },
          {
            "group": "Reponse 200",
            "type": "json",
            "optional": false,
            "field": "data",
            "description": "<p>get qrcode</p>"
          }
        ],
        "Response 400": [
          {
            "group": "Response 400",
            "type": "number",
            "optional": false,
            "field": "status_code",
            "description": "<p>400</p>"
          },
          {
            "group": "Response 400",
            "type": "json",
            "optional": false,
            "field": "message",
            "description": "<p>error description</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request templte ",
          "content": "POST /api/Api500EasyPay/store \n{\n    \"config\":\n        {\n            \"sCorpCode\":\"S001\",\n            \"sOrderID\":\"20170714170127\",\n            \"iUserKey\":\"test123\",\n            \"payment\":\"pay\",\n            \"merNo\":\"QYF201705260107\",\n            \"signKey\":\"2566AE677271D6B88B2476BBF923ED88\",\n            \"encKey\":\"GiWBZqsJ4GYZ8G8psuvAsTo3\",\n            \"payUrl\":\"http:\\/\\/47.90.116.117:90\\/api\\/pay.action\",\n            \"remitUrl\":\"http:\\/\\/47.90.116.117:90\\/api\\/remit.action\"\n        },\n    \"pay\":\n        {\n            \"version\":\"V2.0.0.0\",\n            \"merNo\":\"QYF201705260107\",\n            \"netway\":\"WX\",\"random\":\"7453\",\n            \"orderNum\":\"201707031204515715\",\n            \"amount\":\"100\",\n            \"goodsName\":\"\\u6d4b\\u8bd5\\u652f\\u4ed8WX\",\n            \"charset\":\"utf-8\",\n            \"callBackUrl\":\"http:\\/\\/pay.aosheng.com\\/api\\/Api500EasyPay\\/pay_callback\",\n            \"callBackViewUrl\":\"\"\n         }\n}",
          "type": "json"
        },
        {
          "title": "Response 200 ",
          "content": "HTTP/1.1 200 OK POST /api/Api500EasyPay/store\n{\n   \"merNo\": \"QYF201705260107\",\n   \"msg\": \"提交成功\",\n   \"orderNum\": \"201707031024005063\",\n   \"qrcodeUrl\": \"weixin://wxpay/bizpayurl?pr=XWMdWpG\",\n   \"sign\": \"15811B8CDE231D180776C8E7B352B026\",\n   \"stateCode\": \"00\"\n}",
          "type": "json"
        },
        {
          "title": "Response 400 ",
          "content": "HTTP/1.1 400 Internal Server Error\n{\n   \"status_code\": \"400\",\n   \"message\": {\n       \"stateCode\": \"9999\",\n       \"msg\": \"忙线中, 请稍后再试, 或重新整理\"\n   }\n}",
          "type": "json"
        }
      ]
    }
  },
  {
    "type": "POST",
    "url": "/api/Api500EasyPay/pay_callback",
    "title": "/api/Api500EasyPay/pay_callback",
    "name": "500EasyPay_callback",
    "group": "500EasyPay",
    "version": "1.0.0",
    "description": "<p>500輕易付 callback 接收付款成功或失敗訊息</p>",
    "permission": [
      {
        "name": "POST"
      }
    ],
    "sampleRequest": [
      {
        "url": "http://testpayaosheng.azurewebsites.net/api/Api500EasyPay/pay_callback"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "json",
            "optional": false,
            "field": "data",
            "description": "<p>第三方回傳訊息</p>"
          }
        ],
        "data": [
          {
            "group": "data",
            "type": "string",
            "optional": false,
            "field": "merNo",
            "description": "<p>商戶號</p>"
          },
          {
            "group": "data",
            "type": "string",
            "optional": true,
            "field": "netway",
            "description": "<p>支付网关(支付宝填写ZFB,微信填写WX)</p>"
          },
          {
            "group": "data",
            "type": "string",
            "optional": false,
            "field": "orderNum",
            "description": "<p>商户訂單號</p>"
          },
          {
            "group": "data",
            "type": "string",
            "optional": false,
            "field": "amount",
            "description": "<p>金额（单位：分）</p>"
          },
          {
            "group": "data",
            "type": "string",
            "optional": false,
            "field": "goodsName",
            "description": "<p>商品名稱</p>"
          },
          {
            "group": "data",
            "type": "string",
            "optional": false,
            "field": "payResult",
            "description": "<p>支付状态，00表示成功</p>"
          },
          {
            "group": "data",
            "type": "string",
            "optional": false,
            "field": "payDate",
            "description": "<p>支付时间，格式：yyyyMMddHHmmss</p>"
          },
          {
            "group": "data",
            "type": "string",
            "optional": false,
            "field": "sign",
            "description": "<p>签名（字母大写）</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/Api/V1/Api500EasyPayController.php",
    "groupTitle": "500EasyPay"
  }
] });
