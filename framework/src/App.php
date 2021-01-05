<?php
declare (strict_types = 1);

namespace iflow;

use iflow\console\Console;
use iflow\initializer\annotationInitializer;
use iflow\initializer\Config;
use iflow\initializer\Error;
use iflow\initializer\initializer;

/**
 * Class App
 * @package iflow
 * @property Config $config
 */
class App extends Container
{

    const VERSION = '0.0.1 beta';

    // 用户路由
    public array $routers = [];

    // 应用目录
    protected string $rootPath = '';
    protected string $frameWorkPath = '';
    protected string $appPath = '';
    protected string $runtimePath = '';
    protected string $configExt = '.php';

    // 初始化服务
    protected array $initializers = [
        Config::class,
        Console::class,
        initializer::class,
//        Error::class,
        annotationInitializer::class,
//        HttpServer::class
    ];

    // 用户运行入口类
    public \ReflectionClass $appRunClass;

    // 初始化
    public function __construct(string $rootPath = '')
    {
        $this->frameWorkPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->rootPath    = $rootPath ? rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : $this->getDefaultRootPath();
        $this->appPath     = $this->rootPath . 'app' . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;
        static::setInstance($this);
        $this->instance(static::class, $this);
        $this->instance('iflow\Container', $this);
    }

    public function run(string $class = '') {
        // 反射获取 入口类
        $this->appRunClass = new \ReflectionClass($class) ?: throw new \Error('初始化失败');
        // 初始化 全局依赖
        $this->initializer();
    }

    public function initializer() : void
    {
        // 加载基础服务
        $this->load();
        foreach ($this->initializers as $key) {
            $this->make($key) -> initializer($this);
        }
    }

    public function getVerSion() : string
    {
        return self::VERSION;
    }

    // 加载全局配置文件
    public function load() : void
    {
        // 加载助手函数
        include_once $this->frameWorkPath . 'src/helper.php';

        if (is_file($this->appPath . '/common.php')) {
            include_once $this->appPath . '/common.php';
        }
    }

    /**
     * 获取应用根目录
     * @access protected
     * @return string
     */
    public function getDefaultRootPath(): string
    {
        return dirname($this->frameWorkPath) . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取配置路径
     * @return string
     */
    public function getConfigPath() : string
    {
        return $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取配置文件尾缀
     * @return string
     */
    public function getConfigExt(): string
    {
        return $this->configExt;
    }

    /**
     * 获取框架运行路径
     * @return string
     */
    public function getFrameWorkPath() : string
    {
        return $this->frameWorkPath;
    }

    /**
     * 获取运行路径
     * @return string
     */
    public function getRuntimePath() : string
    {
        return $this->runtimePath;
    }

    public function getAppPath() : string
    {
        return $this->appPath;
    }

    public function getRootPath() : string
    {
        return $this->rootPath;
    }
}