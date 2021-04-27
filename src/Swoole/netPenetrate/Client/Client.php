<?php


namespace iflow\Swoole\netPenetrate\Client;


use iflow\Swoole\netPenetrate\Service;

class Client extends Service
{

    public function start()
    {
        // TODO: Implement start() method.
        $this->client = new \Swoole\Coroutine\Client($this->config['sockType']);
        $scheduler = new \Swoole\Coroutine\Scheduler;
        $scheduler->add(function () {
            if (!$this->client -> connect(
                $this->config['server']['host'],
                $this->config['server']['port'],
                0.5
            )) {
                $this->netPenetrate -> Console -> outPut -> writeLine('Connection Server Fail');
            } else {
                $this->client -> set($this->config['SwConfig']);
                $this->outServerAddress();
                while ($this->client -> isConnected()) {
                    $pack = $this->client -> recv(-1);
                    if ($pack === "") break;
                    $pack = trim($pack, "\r\n");
                    $pack = json_decode($pack, true);
                    go(function () use ($pack){
                        if (isset($pack['action']) && $pack['action'] === 'new') {
                            if ($this->connectionTunnel() === false) {
                                logs('error', 'Tunnel Server Connection Fail');
                            } else {
                                $this->send($this->tunnel, $pack['fd']);
                                $local = $this->localConnection();
                                if ($local !== false) {
                                    go(function () use ($local){
                                        while (true) {
                                            $pack = $this->tunnel->recv(-1);
                                            if ($pack === "") break;
                                            $this->send($local, $pack);
                                        }
                                        $this->tunnel->close();
                                    });

                                    go(function () use ($local) {
                                        while (true) {
                                            $pack = $local->recv(-1);
                                            if ($pack === "") break;
                                            $this->send($this->tunnel, $pack);
                                        }
                                        $local->close();
                                    });
                                }
                            }
                        }
                    });
                }
                $this->client -> close();
            }
        });
        $scheduler->start();
    }

    private function connectionTunnel(): \Swoole\Coroutine\Client | bool
    {
        $this -> tunnel = new \Swoole\Coroutine\Client($this->config['sockType']);
        $this -> tunnel -> connect($this->config['tunnel']['host'], $this->config['tunnel']['port'], 0.5);
        return $this->tunnel -> isConnected() ? $this->tunnel : false;
    }

    private function localConnection(): \Swoole\Coroutine\Client | bool
    {
        $local = new \Swoole\Coroutine\Client($this->config['sockType']);
        $local -> connect(
            $this->config['local']['host'], $this->config['local']['port'], 0.5
        );
        return $local -> isConnected() ? $local : false;
    }

    private function send(\Swoole\Coroutine\Client $client, $data)
    {
        return $client -> send($data);
    }
}