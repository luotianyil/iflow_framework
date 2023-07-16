<?php

namespace iflow\swoole\implement\Client\Rpc\Annotation;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\swoole\implement\Client\Rpc\implement\interfaces\ProxyRpcInterface;
use iflow\swoole\implement\Client\Rpc\implement\RpcClient;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class RegisterRpcInterface extends AnnotationAbstract {

    public function __construct(protected array $options = []) {
    }

    /**
     * 将当前接口信息注入容器
     * @param Reflector $reflector
     * @return ProxyRpcInterface
     * @throws InvokeClassException
     */
    public function register(Reflector $reflector): ProxyRpcInterface {
        $proxy = (new RpcClient()) -> setRequestBaseUrl($this->options['requestBaseUrl'] ?? '')
          -> setRpcServerName($this->options['rpc'] ?? '');

        return app() -> register($reflector -> getName(), $proxy);
    }

    public function process(Reflector $reflector, &$args): ProxyRpcInterface {
        // TODO: Implement process() method.
        return $this -> register($reflector);
    }
}