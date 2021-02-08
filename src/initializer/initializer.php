<?php


namespace iflow\initializer;


use iflow\App;
use Co;
use Swoole\Runtime;
use think\facade\Db;
use Yurun\Util\Swoole\Guzzle\SwooleHandler;
use GuzzleHttp\DefaultHandler;

class initializer
{

    public function initializer(App $app)
    {
        // 初始化全局依赖
        Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
        DefaultHandler::setDefaultHandler(SwooleHandler::class);
        Db::setConfig(config('database'));
    }

}