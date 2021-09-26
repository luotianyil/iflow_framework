<?php


namespace iflow\initializer;


use iflow\App;
use iflow\i18n\I18n;
use think\facade\Db;

class initializer
{

    protected array $iniInitializerArray = [
        'timezone' => 'date_default_timezone_set'
    ];

    public function initializer(App $app)
    {
        // 初始化全局依赖
       if (swoole_success()) {
           \Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);
           \GuzzleHttp\DefaultHandler::setDefaultHandler(\Yurun\Util\Swoole\Guzzle\SwooleHandler::class);
       }
       Db::setConfig(config('database'));
       app(I18n::class) -> initializer($app);
       $this->ini_initializer();
    }

    /**
     * 初始化php.ini配置
     * @return $this
     */
    protected function ini_initializer(): static
    {
        $ini = config('ini');

        if (empty($ini)) return $this;

        foreach ($this->iniInitializerArray as $iniKey => $iniAction) {
            if (isset($ini[$iniKey])) {
                $iniValue = !is_array($ini[$iniKey]) ? [$ini[$iniKey]] : $ini[$iniKey];
                call_user_func($iniAction, ...$iniValue);
            }
        }
        return $this;
    }
}