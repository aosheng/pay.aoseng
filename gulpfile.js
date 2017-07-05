// 產生Api文件
var elixir = require('laravel-elixir');
require('laravel-elixir-apidoc');

elixir(function (mix) {
    mix.apidoc();
});