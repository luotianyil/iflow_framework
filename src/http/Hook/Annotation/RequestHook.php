<?php

namespace iflow\http\Hook\Annotation;

use Attribute;
use Reflector;
use iflow\Response;
use iflow\Pipeline\Pipeline;
use Psr\Http\Message\ResponseInterface;
use iflow\exception\Adapter\ErrorException;
use iflow\exception\Adapter\HttpResponseException;
use iflow\http\Hook\Interfaces\RequestHookInterface;
use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;

#[Attribute(Attribute::TARGET_CLASS)]
class RequestHook extends AnnotationAbstract {

    #[Inject]
    protected Pipeline $hookQueue;

    protected string $hookName = 'RequestHook';

    protected array $hookEvents = [];

    public function __construct() {}

    /**
     * @param Reflector $reflector
     * @param $args
     * @return mixed
     * @throws InvokeClassException|InvokeFunctionException
     */
    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.

        if (!in_array(RequestHookInterface::class, $reflector -> getInterfaceNames())) {
            throw new \RuntimeException($reflector -> getName() . ' instanceof RequestHookInterface fail');
        }

        if (!app() -> has(RequestHook::class)) app() -> register(RequestHook::class, $this);

        return app(RequestHook::class) -> setRequestHookQueue($this->hookName, function (array $args, ?callable $next = null) use ($reflector) {

            $callback = app() -> invoke([ $reflector -> newInstance(), 'handle' ], $args);
            if ($callback instanceof Response || $callback instanceof  ResponseInterface) {
                throw new HttpResponseException($callback);
            }
            $next($args);
        });
    }


    /**
     * 设置队列
     * @param string $hookName
     * @param callable $hook
     * @return $this
     */
    protected function setRequestHookQueue(string $hookName, callable $hook): RequestHook {
        if (empty($this->hookEvents[$hookName])) $this->hookEvents[$hookName] = [];
        $this->hookEvents[$hookName][] = $hook;
        return $this;
    }


    /**
     * @param string $hookName
     * @param ...$args
     * @return mixed
     * @throws ErrorException
     */
    public function trigger(string $hookName, ...$args): mixed {
        try {
            $hookEvents = $this -> hookEvents[$hookName] ?? [];
            if (empty($hookEvents)) return true;

             $this -> hookQueue -> through($hookEvents) -> process([app(), ...$args]);
            return true;
        } catch (\Exception $exception) {
            if ($exception instanceof HttpResponseException) return $exception -> getResponse();

            throw new ErrorException(
                $exception -> getCode(), $exception -> getMessage(),
                $exception -> getFile(), $exception -> getLine()
            );
        }
    }
}
