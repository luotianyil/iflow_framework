<?php


namespace iflow\initializer;


use iflow\App;
use think\facade\Db;

class initializer
{

    public function initializer(App $app)
    {
        // 初始化全局依赖
       if (extension_loaded('swoole') && is_cli()) {
           \Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
           \GuzzleHttp\DefaultHandler::setDefaultHandler(\Yurun\Util\Swoole\Guzzle\SwooleHandler::class);
       }
        Db::setConfig(config('database'));
    }

}