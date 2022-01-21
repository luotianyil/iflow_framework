<?php


namespace iflow\initializer;


use GuzzleHttp\DefaultHandler;
use iflow\App;
use iflow\i18n\I18n;
use think\facade\Db;
use Yurun\Util\Swoole\Guzzle\SwooleHandler;

class initializer {

    protected array $iniInitializerArray = [
        'timezone' => 'date_default_timezone_set'
    ];

    public function initializer(App $app) {
        // 初始化全局依赖
       if (swoole_success()) {
           \Co::set([ 'hook_flags' => SWOOLE_HOOK_ALL ]);
           DefaultHandler::setDefaultHandler(SwooleHandler::class);
       }
       $app -> make(I18n::class) -> initializer($app);
       $this -> ini_initializer()
             -> db_initializer()
             -> routerConfigInitializer()
             -> initializerAnnotation($app);
    }

    protected function initializerAnnotation(App $app) {
        $app -> execute($app::class);
    }

    /**
     * 初始化数据库
     * @return $this
     */
    protected function db_initializer(): initializer {
        return config('database',
            call: fn ($config) => !empty($config) ? (Db::setConfig($config) ?? $this) : $this
        );
    }

    /**
     * 初始化php.ini配置
     * @return $this
     */
    protected function ini_initializer(): initializer {
        return config('ini', call: function ($ini) {
            if (empty($ini)) return $this;
            foreach ($ini as $iniConfigName => $option) {
                $iniAction = $this->iniInitializerArray[$iniConfigName] ?? $iniConfigName;
                call_user_func($iniAction, ...is_array($option) ? $option : [$option]);
            }
            return $this;
        });
    }

    /**
     * 初始化路由配置
     * @return $this
     */
    protected function routerConfigInitializer(): initializer {
        $router = config('app@router');
        $config = app(\iflow\Router\implement\Config::class, config('app@router'));
        $router = array_merge($router, [ 'router' => [], 'routerParams' => [] ]);
        $config -> setRouters($router);
        return $this;
    }
}