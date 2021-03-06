<?php
declare (strict_types = 1);

namespace iflow;

use iflow\console\Console;
use iflow\event\Event;
use iflow\initializer\annotationInitializer;
use iflow\initializer\appMonitoring;
use iflow\initializer\appSurroundings;
use iflow\initializer\Config;
use iflow\initializer\Error;
use iflow\initializer\initializer;
use iflow\log\Log;

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
    protected string $appClassName = '';
    protected string $runtimePath = '';
    protected string $configExt = '.php';

    // 初始化服务
    protected array $initializers = [
        Config::class,
        Log::class,
        Error::class,
        appSurroundings::class,
        annotationInitializer::class,
        initializer::class,
        Event::class,
        Console::class,
        appMonitoring::class
    ];

    protected array $frameWorkFolder = [
        'app',
        'runtime',
        'config',
        'public'
    ];

    // 用户运行入口类
    public \ReflectionClass $appRunClass;

    // 初始化
    public function __construct()
    {
        static::setInstance($this);
        $this->appClassName = static::class;
        $this->instance($this->getAppClassName(), $this);
        $this->instance('iflow\Container', $this);
    }

    public function run() {
        // 反射获取 入口类
        $this->appRunClass = new \ReflectionClass($this->getAppClassName());
        if ($this->frameWorkDirInit()) {
            // 初始化 全局依赖
            $this->initializer();
        }
    }

    protected function initializer() : void
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
        include_once $this->frameWorkPath . DIRECTORY_SEPARATOR . 'helper.php';

        if (is_file($this->appPath . DIRECTORY_SEPARATOR . 'common.php')) {
            include_once $this->appPath . DIRECTORY_SEPARATOR . 'common.php';
        }
    }

    public function frameWorkDirInit(): bool
    {
        $this->frameWorkPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->rootPath    = $this->getDefaultRootPath();
        $this->appPath     = $this->rootPath . 'app' . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;
        foreach ($this->frameWorkFolder as $key) {
            if (!file_exists($this->getDefaultRootPath() . $key)) {
                throw new \Exception("application rootPath file / folder : ". $key. " not exists");
            }
        }
        return true;
    }

    /**
     * 获取应用根目录
     * @access protected
     * @return string
     */
    public function getDefaultRootPath(): string
    {
//        return dirname($this->frameWorkPath, 3) . DIRECTORY_SEPARATOR;
        return $this->frameWorkPath;
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

    public function getAppClassName(): string
    {
        return $this->appClassName;
    }
}