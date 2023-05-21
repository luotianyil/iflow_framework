<?php


namespace iflow\initializer;

use iflow\App;
use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\exception\Configure;
use iflow\exception\Handle;
use iflow\exception\Adapter\ErrorException;
use iflow\exception\Adapter\RenderDebugView;
use iflow\Response;
use Throwable;

// 异常接管
class Error {

    protected App $app;

    protected array $config = [];

    protected string $handle = Handle::class;

    #[Inject]
    protected Configure $configure;

    /**
     * 初始化异常处理
     * @param App $app
     * @return void
     */
    public function initializer(App $app) {
        $this->app = $app;
        $this->config = config('app');

        $this->handle = $this->config['exceptionHandle'] ?? $this->handle;

        // 全部接管
        error_reporting(E_ALL);
        set_exception_handler([ $this, 'appHandler' ]);
        set_error_handler([ $this, 'appError' ]);

        // 错误终止
        register_shutdown_function([ $this, 'appShuDown' ]);
    }

    /**
     * 致命错误处理
     * @param int $errno
     * @param string $message
     * @param string $file
     * @param int $line
     * @return void
     * @throws ErrorException
     */
    public function appError(int $errno, string $message, string $file = '', int $line = 0): void {
        $exception = new ErrorException($errno, $message, $file, $line);
        if (error_reporting() & $errno) {
            throw $exception;
        }
    }

    /**
     * 错误回调处理
     * @param Throwable $e
     * @return mixed
     * @throws \ReflectionException|InvokeClassException
     */
    public function appHandler(Throwable $e): mixed {

        $renderDebugView = new RenderDebugView($e, $this->config);

        // 是否配置 异常接管
        if ($this -> configure -> configure($e::class, $e, $renderDebugView)) {
            return true;
        }

        // 异常处理回调
        $res = class_exists($this->handle) ?
            (new $this->handle(
                $this->configure -> getFatalType()
            )) -> render($this->app, $e) : $renderDebugView -> render($e);

        if ($res instanceof Response) return $res -> send();
        return $res;
    }

    /**
     * 异常程序结束回调
     * @return void
     * @throws ErrorException
     */
    public function appShuDown(): void {
        if (!is_null($error = error_get_last()) && $this-> configure -> isFatal($error['type'])) {
            throw new ErrorException(0, $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * 新增自定义异常接管类
     * @param string $exceptionClazz 接管的异常
     * @param string $class
     * @param array $args
     * @return void
     */
    public function setTakeoverConfigure(string $exceptionClazz, string $class, array $args = []): void {
        $this->configure -> setConfigure(...func_get_args());
    }
}