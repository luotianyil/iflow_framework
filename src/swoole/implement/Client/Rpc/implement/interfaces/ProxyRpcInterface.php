<?php

namespace iflow\swoole\implement\Client\Rpc\implement\interfaces;

interface ProxyRpcInterface {

    /**
     * 设置RPC服务端地址
     * @param string $rpcServerIp
     * @param int $rpcServerPort
     * @return ProxyRpcInterface
     */
    public function setRpcServer(string $rpcServerIp, int $rpcServerPort): ProxyRpcInterface;

    /**
     * 设置服务端名称
     * @param string $rpcServerName
     * @return ProxyRpcInterface
     */
    public function setRpcServerName(string $rpcServerName): ProxyRpcInterface;

    /**
     * 设置请求基础地址
     * @param string $requestBaseUrl
     * @return ProxyRpcInterface
     */
    public function setRequestBaseUrl(string $requestBaseUrl): ProxyRpcInterface;

    /**
     * 设置RPC 调用是否SSL加密
     * @param bool $isSsl
     * @return ProxyRpcInterface
     */
    public function setIsSsl(bool $isSsl): ProxyRpcInterface;

    /**
     * 请求服务器获取数据
     * @param string $name 请求方法名称
     * @param array $params 请求参数
     * @return mixed
     */
    public function request(string $name, array $params): mixed;

}