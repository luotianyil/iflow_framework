<?php

namespace iflow\exception;

use iflow\exception\Adapter\RenderDebugView;
use iflow\Pipeline\Pipeline;
use iflow\Response;
use Psr\Http\Message\ResponseInterface;

class Configure {

    protected \Throwable $throwable;

    public Pipeline $pipeline;

    protected RenderDebugView $renderDebugView;

    protected string $fatalType;

    public function __construct(protected $configure = []) {
    }

    public function configure(string $exceptionClazz, \Throwable $throwable, RenderDebugView $renderDebugView): bool {
        $thrace = $this->configure[$exceptionClazz] ?? [];
        if (count($thrace) === 0) {
            $thrace = $this->configure[\Throwable::class] ?? [];
        }

        $this->throwable = $throwable;
        $this->renderDebugView = $renderDebugView;
        $this->fatalType = $this->isFatal($throwable -> getCode()) ? 'warning' : 'error';

        return $this->AppExceptionDownHandler($thrace);
    }

    protected function AppExceptionDownHandler(array $thrace): bool {

        if (empty($thrace)) return false;

        $isEnd = true;

        $pipeline = new Pipeline();
        $pipeline -> through(array_map(function (array $clazz) {
            return function ($app, $next) use ($clazz) {
                $callback = app() -> invoke(
                    [$app -> make($clazz[0]), 'configure'],
                    [ $this->throwable, $app, $next, $clazz[1] ]
                );
                if ($callback instanceof Response || $callback instanceof ResponseInterface) {
                    $this->renderDebugView -> render($callback) -> send();
                }
            };
        }, $thrace));

        $pipeline -> process(app(), function () use (&$isEnd) {
            //TODO: 当自定义 异常接管执行完毕时 执行框架默认接管方法
            $isEnd = false;
        });

        return $isEnd;
    }

    /**
     * 设置 接管异常
     * @param string $exceptionClazz
     * @param string $class
     * @param array $args
     * @return void
     */
    public function setConfigure(string $exceptionClazz, string $class, array $args = []) {
        if (empty($this->configure[$exceptionClazz])) {
            $this->configure[$exceptionClazz] = [];
        }
        $this->configure[$exceptionClazz][] = [ $class, $args ];
    }


    public function isFatal(int $type): bool {
        return in_array($type, [ E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_RECOVERABLE_ERROR, E_STRICT, E_NOTICE, E_DEPRECATED ]);
    }

    /**
     * 获取异常类型
     * @return string
     */
    public function getFatalType(): string {
        return $this->fatalType;
    }

}