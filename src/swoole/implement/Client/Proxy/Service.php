<?php

namespace iflow\swoole\implement\Client\Proxy;

use Swoole\Coroutine\Client;
use Swoole\Coroutine\Scheduler;
use iflow\swoole\implement\Client\Tcp\Service as TCPService;

class Service extends TCPService {

    protected Client $server;

    protected Scheduler $scheduler;

    public function start() {
        $this -> scheduler = new Scheduler();
        $this -> scheduler -> add(function () {
            $this->server = $this -> connection('server');
            $this -> consumption();
        });

        $this->scheduler -> start();
    }

    protected function consumption(): bool {
        if (!$this->server -> isConnected()) {
            $this -> servicesCommand -> Console -> writeConsole -> writeLine('Tunnel Server Connection Fail');
            return false;
        }

        while ($this->server -> isConnected()) {
            if (($pack = $this -> getPack(packFormatter: false)) === '') break;
            go(function () use ($pack) {
                $local = $this -> connection('local');
                $tunnel = $this -> connection('tunnel');

                $connectionStatus = $this -> checkTunnelConnection($local, $tunnel, function () use ($local, $tunnel, $pack) {
                    // 关联远程客户端标识
                    if (intval($pack)) $tunnel -> send($pack);

                    // 转发数据至本地服务端
                    go(function () use ($local, $tunnel) {
                        $this -> getAllPack($tunnel, fn ($pack) => $pack && $local -> send($pack), true);
                    });

                    // 转发响应数据至隧道
                    go(function () use ($local, $tunnel) {
                        $this -> getAllPack($local, fn ($pack) => $pack && $tunnel -> send($pack), true);
                    });

                    return true;
                });

                if (!$connectionStatus) {
                    $this -> servicesCommand -> Console -> writeConsole -> writeLine('Remote Tunnel Server Connection Fail');
                }
            });
        }

        $this -> servicesCommand -> Console -> writeConsole -> writeLine('Remote Tunnel Server Close Connection');
        return true;
    }

    /**
     * 获取响应信息
     * @param Client|null $client
     * @param bool $packFormatter
     * @return string|array|false
     */
    protected function getPack(?Client $client = null, bool $packFormatter = true): string|array|false {
        $pack = ($client ?: $this-> server) -> recv(-1);
        if (!$packFormatter) return $pack;

        if ($pack === '') return false;
        return json_decode(trim(trim($pack, "\r\n"), $this -> config -> getPackageEof()), true)?: false;
    }

    /**
     * 获取所有数据
     * @param Client|null $client
     * @param callable|null $callable
     * @param bool $isClose
     * @return void
     */
    protected function getAllPack(?Client $client = null, ?callable $callable = null, bool $isClose = false): void {
        while (true) {
            $pack = $this -> getPack($client, false);
            if ($pack === false || $pack === '') {
                $isClose && $this -> close($client);
                break;
            }
            if (is_callable($callable)) call_user_func($callable, $pack, $client);
        }
    }

    /**
     * 验证链接状态
     * @param Client $local
     * @param Client $tunnel
     * @param callable $callable
     * @return bool
     */
    protected function checkTunnelConnection(Client $local, Client $tunnel, callable $callable): bool {
        if ($local -> isConnected() && $tunnel -> isConnected()) return $callable();
        return false;
    }

    /**
     * 关闭连接
     * @param Client|null $client
     * @return void
     */
    protected function close(?Client $client): void {
        ($client ?: $this->server) -> close();
    }

}