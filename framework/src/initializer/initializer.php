<?php


namespace iflow\initializer;


use iflow\App;

use Co;

class initializer
{

    public function initializer(App $app)
    {
        // 初始化全局依赖
        Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
    }

}