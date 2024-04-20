<?php

namespace iflow\exception;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\exception\Adapter\RenderDebugView;
use iflow\exception\Annotation\ExceptionHandler;
use iflow\Pipeline\Pipeline;
use iflow\Response;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Throwable;

class Configure {

    protected Throwable $throwable;

    public Pipeline $pipeline;

    protected RenderDebugView $renderDebugView;

    protected string $fatalType;

    public function __construct(protected array|string $configure = []) {
    }

    /**
     * 异常处理
     * @param string $exceptionClazz
     * @param Throwable $throwable
     * @param RenderDebugView $renderDebugView
     * @return bool
     * @throws ReflectionException|InvokeClassException
     */
    public function configure(string $exceptionClazz, Throwable $throwable, RenderDebugView $renderDebugView): bool {
        $thrace = $this->checkExceptionHandler($exceptionClazz, $throwable) ?: ($this->configure[$exceptionClazz] ?? []);

        if (count($thrace) === 0) {
            $thrace = $this->configure[Throwable::class] ?? [];
        }

        $this->throwable = $throwable;
        $this->renderDebugView = $renderDebugView;
        $this->fatalType = $this->isFatal($throwable -> getCode()) ? 'warning' : 'error';

        return $this->AppExceptionDownHandler($thrace);
    }


    /**
     * 检查是否使用自定义异常处理
     * @param string $exceptionClazz
     * @param Throwable $throwable
     * @return array
     * @throws ReflectionException
     */
    protected function checkExceptionHandler(string $exceptionClazz, Throwable $throwable): array {
        $trace = $throwable -> getTrace()[0];
        [ $class, $method ] = [ $trace['class'], $trace['function'] ];
        $ref = new \ReflectionClass($class);

        $thraceClassArr = [];
        if ($attributes = (
            $ref -> hasMethod($method) ? $ref -> getMethod($method) -> getAttributes(ExceptionHandler::class) : null
        )) {
            foreach ($attributes as $attribute) {
                $attrObject = $attribute -> newInstance();
                array_push($thraceClassArr, ...$attrObject -> process($ref, $exceptionClazz, $throwable));
            }
        }

        return $thraceClassArr;
    }

    /**
     * 执行异常
     * @param array $thrace
     * @return bool
     * @throws InvokeClassException
     */
    protected function AppExceptionDownHandler(array $thrace): bool {

        if (empty($thrace)) return false;

        $isEnd = true;

        $pipeline = new Pipeline();
        $pipeline -> through(array_map(function (array|string $clazz) {
            $clazz = is_array($clazz) ? $clazz : [ $clazz ];
            return function ($app, $next) use ($clazz) {
                $callback = app() -> invoke(
                    [ $app -> make($clazz[0]), 'configure' ],
                    [ $this->throwable, $app, $next, $clazz[1] ?? [] ]
                );
                if ($callback instanceof Response || $callback instanceof ResponseInterface) {
                    $this->renderDebugView -> render($callback) -> send();
                }
            };
        }, $thrace));

        $pipeline -> process(app(), function () use (&$isEnd) {
            // TODO: 当自定义 异常接管执行完毕时 执行框架默认接管方法
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
    public function setConfigure(string $exceptionClazz, string $class, array $args = []): void {
        if (empty($this->configure[$exceptionClazz])) {
            $this->configure[$exceptionClazz] = [];
        }
        $this->configure[$exceptionClazz][] = [ $class, $args ];
    }

    /**
     * 获取异常类型
     * @param int $type
     * @return bool
     */
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