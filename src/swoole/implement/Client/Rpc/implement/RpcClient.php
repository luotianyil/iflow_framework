<?php

namespace iflow\swoole\implement\Client\Rpc\implement;

use iflow\swoole\implement\Client\Rpc\implement\interfaces\ProxyRpcInterface;
use iflow\swoole\implement\Commounity\Rpc\Request\Request;

class RpcClient implements ProxyRpcInterface {

    protected string $rpcServerIp = '';

    protected int $rpcServerPort = 0;

    protected string $rpcServerName = '';

    protected bool $isSsl = false;

    protected Request $rpcRequest;

    protected string $requestBaseUrl = '';


    /**
     * 设置RPC服务端地址
     * @param string $rpcServerIp
     * @param int $rpcServerPort
     * @return ProxyRpcInterface
     */
    public function setRpcServer(string $rpcServerIp, int $rpcServerPort): ProxyRpcInterface {
        // TODO: Implement setRpcServerUrl() method.
        $this->rpcServerIp= $rpcServerIp;
        $this->rpcServerPort = $rpcServerPort;
        return $this;
    }

    /**
     * 设置服务端名称
     * @param string $rpcServerName
     * @return ProxyRpcInterface
     */
    public function setRpcServerName(string $rpcServerName): ProxyRpcInterface {
        // TODO: Implement setRpcServerName() method.
        $this->rpcServerName = $rpcServerName;
        return $this;
    }

    /**
     * 设置请求基础地址
     * @param string $requestBaseUrl
     * @return ProxyRpcInterface
     */
    public function setRequestBaseUrl(string $requestBaseUrl): ProxyRpcInterface {
        // TODO: Implement setRequestBaseUrl() method.
        $this->requestBaseUrl = $requestBaseUrl;
        return $this;
    }

    /**
     * @param string $rpcServerName
     * @return ProxyRpcInterface
     */
    public function setIsSsl(string $rpcServerName): ProxyRpcInterface {
        // TODO: Implement setIsSsl() method.
        $this->isSsl = true;
        return $this;
    }

    /**
     * 请求服务器获取数据
     * @param string $name
     * @param array $params
     * @return string|array
     */
    public function request(string $name, array $params): string|array {
        // TODO: Implement request() method.

        $params['client_name'] = $this->rpcServerName;

        $this->rpcRequest = rpc(
            $this->rpcServerIp, $this->rpcServerPort,
            "{$this -> requestBaseUrl}/{$name}",
            $this->isSsl, $params
        );

        return $this->rpcRequest -> getData();
    }

    /**
     * 请求远程地址
     * @param string $name
     * @param array $arguments
     * @return string|array
     */
    public function __call(string $name, array $arguments): string|array {
        // TODO: Implement __call() method.
        return $this -> request($name, $arguments);
    }

}