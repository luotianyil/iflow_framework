<?php

namespace iflow\swoole\implement\Client\Rpc\Annotation;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\swoole\implement\Client\Rpc\implement\interfaces\ProxyRpcInterface;
use iflow\swoole\implement\Client\Rpc\implement\RpcClient;
use iflow\Utils\Generate\GeneratePhp\GeneratePhpClass;
use iflow\Utils\Generate\GeneratePhp\GeneratePhpClassException;
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

        $proxy = $this -> getProxy($reflector)
            -> setRequestBaseUrl($this->options['requestBaseUrl'] ?? '')
            -> setRpcServerName($this->options['rpcServerName'] ?? '')
            -> setIsSsl($this->options['isSsl'] ?? false);

        if (isset($this->options['rpcServer'])) {
            $proxy -> setRpcServer($this->options['rpcServer']['host'], $this->options['rpcServer']['port']);
        }

        return app() -> register($reflector -> getName(), $proxy);
    }

    public function process(Reflector $reflector, &$args): ProxyRpcInterface {
        // TODO: Implement process() method.
        return $this -> register($reflector);
    }

    /**
     * 获取代理对象
     * @param Reflector $reflector
     * @return ProxyRpcInterface
     * @throws InvokeClassException
     * @throws GeneratePhpClassException
     */
    protected function getProxy(Reflector $reflector): object {

        $proxyPath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, app() -> getRootPath('rpc_service'));
        if (!is_dir($proxyPath)) mkdir($proxyPath);

        $className = explode('\\', $reflector -> getName());

        return (new GeneratePhpClass(
            namespace: 'app\\rpc_service',
            className: array_pop($className),
            extend: RpcClient::class,
            implements: [ $reflector -> getName() ],
            args: [],
            method: fn() => 'return $this -> request(__FUNCTION__, func_get_args());',
            saveToFolder: $proxyPath
        )) -> import();
    }
}