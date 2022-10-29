<?php
declare (strict_types = 1);

namespace iflow;

use iflow\console\Console;
use iflow\Container\Container;
use iflow\Container\implement\annotation\traits\Execute;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\event\Event;
use iflow\initializer\{appSurroundings, Config, Error, Helpers, initializer};
use iflow\log\Log;

/**
 * Class App
 * @mixin Container
 * @package iflow
 */
abstract class App {

    final protected const version = '0.0.1 beta';

    // 用户路由
    public array $routers = [];

    // 应用目录
    protected string $rootPath = '';
    protected string $frameWorkPath = '';
    protected string $appPath = '';
    protected string $runtimePath = '';
    protected string $configExt = '.php';

    // 执行时间
    protected float $startTimes = 0.00;

    // 初始化服务
    protected array $initializers = [
        Config::class,
        Helpers::class,
        appSurroundings::class,
        Event::class,
        Log::class,
        Error::class,
        initializer::class,
        Console::class
    ];

    protected array $frameWorkFolder = [ 'app', 'runtime', 'config', 'public' ];

    abstract public function runApp();

    /**
     * 初始化基础方法
     * @return void
     * @throws \Exception
     */
    public function run() {
        $this -> register('iflow\\App', $this);
        if ($this -> frameWorkDirInit()) {
            $this -> load() -> initializer();
        }
    }

    public function execute(string $class) {
        $execute = new Execute();
        $ref = new \ReflectionClass($class);
        $execute -> getReflectorAttributes($ref) -> execute($ref);
    }

    /**
     * 加载基础服务
     * @return $this
     * @throws InvokeClassException
     */
    public function initializer(): App {
        $this -> boot();
        return $this;
    }

    /**
     * 加载基础类
     * @return $this
     * @throws InvokeClassException
     */
    protected function boot(): App {
        array_walk($this->initializers, function ($value) {
            $this->make($value) -> initializer($this);
        });
        return $this;
    }

    /**
     * 获取当前版本信息
     * @return string
     */
    public function getVerSion() : string {
        return self::version;
    }

    /**
     * 加载全局配置文件
     * @return $this
     */
    protected function load() : static {
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'helper/helper.php';
        return $this;
    }

    protected function frameWorkDirInit(): bool {
        $this->frameWorkPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        $this->rootPath    = $this->getDefaultRootPath();
        $this->appPath     = $this->rootPath . 'app' . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;
         array_map(function (string $file): string {
           if (!file_exists($this->getDefaultRootPath() . $file)) {
               throw new \Exception("file $file dose not exists");
           }
           return $file;
        }, $this->frameWorkFolder);
        return true;
    }

    public function isDebug(): bool {
        return config('app@debug', false);
    }

    /**
     * 获取应用根目录
     * @return string
     */
    public function getDefaultRootPath(): string {
        return dirname($this->frameWorkPath, 3) . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取配置路径
     * @return string
     */
    public function getConfigPath() : string {
        return $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取配置文件尾缀
     * @return string
     */
    public function getConfigExt(): string {
        return $this->configExt;
    }

    /**
     * 获取框架运行路径
     * @return string
     */
    public function getFrameWorkPath() : string {
        return $this->frameWorkPath;
    }

    /**
     * 获取运行路径
     * @return string
     */
    public function getRuntimePath() : string {
        return $this->runtimePath;
    }

    public function getAppPath() : string {
        return $this->appPath;
    }

    public function getRootPath() : string {
        return $this->rootPath;
    }

    public function getAppClassName(): string {
        return $this::class;
    }

    /**
     * @param float $startTimes
     * @return App
     */
    public function setStartTimes(float $startTimes): App {
        $this->startTimes = $startTimes;
        return $this;
    }

    /**
     * @return float
     */
    public function getStartTimes(): float {
        return $this->startTimes;
    }

    public function __call(string $name, array $arguments) {
        // TODO: Implement __call() method.
        return call_user_func([ Container::getInstance(), $name ], ...$arguments);
    }
}