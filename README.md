# Aoshang 支付系統 V1

**Laravel 5.4** 作為主要框架
**Docker** 建立環境

## 系統配置

- PHP 7.0.20
- Nginx 1.9.0
- Redis 3.2.29
- Mysql 5.6.36

## 內含服務

- [Dingo-api](http://laravelacademy.org/post/3822.html) Api管理
- [Supervisord](http://supervisord.org/) worker進程管理
- [ApiDoc](http://apidocjs.com/) Api說明文檔
- [Guzzle](http://docs.guzzlephp.org/en/stable/) 發送http請求, 可符合PSR-7
- [Monolog](https://seldaek.github.io/monolog/) 配合[Logviewer](https://github.com/rap2hpoutre/laravel-log-viewer), 記錄街口事件
- [Sentry](Sentry.io) 程式報錯機制, 可配合Email和簡訊及時通知
- Xdbug php除錯器
- PHPunit 測試

## How To Use

- `git clone git@github.com:aosheng/pay.aosheng.git`

- [install Docker for Mac](https://docs.docker.com/docker-for-mac/install/)

- docker-compose up -d

- 建立.env, copy .env_example

- cli 輸入以下指令
    ``` 
    $ composer update
    
    $ npm install
    
    $ gulp
    ```
- 添加localhost

`sudo vim /etc/hosts 加入 127.0.0.1 pay.aosheng.com`
