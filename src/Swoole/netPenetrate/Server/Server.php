<?php


namespace iflow\Swoole\netPenetrate\Server;


use iflow\Swoole\netPenetrate\Server\event\listen;
use iflow\Swoole\netPenetrate\Server\event\tunnel;
use iflow\Swoole\netPenetrate\Service;
use iflow\Utils\Tools\Timer;
use Swoole\Coroutine\Channel;

class Server extends Service
{

    public Channel $serverChannel;
    public Channel $localChannel;
    public Channel $tunnelChannel;

    public function start()
    {
        // TODO: Implement start() method.
        $this->serverChannel = new Channel(10);
        $this->localChannel = new Channel(10);
        $this->tunnelChannel = new Channel(10);

        $this -> listen()
              -> startServer()
              -> startTunnel();
        $this->listen -> start();
    }

    private function listen(): static
    {
        $this -> listen = new \Swoole\Server(
            $this->config['listen']['host'],
            $this->config['listen']['port'],
            $this->config['listen']['sockType']
        );

        $this->listen -> set($this->config['listen']['SwConfig']);

        $event = new listen($this);
        $this->on($this->listen, $event);

        $this->listen -> on('WorkerStart', function () {
            $this->outServerAddress();
        });
        $this->consumption(function () {
            if (!$this->localChannel -> isEmpty()) {
                $data = $this->localChannel -> pop();
                $this->listen -> send($data['fd'], $data['data']);
            }
        });
        return $this;
    }

    private function startServer(): static
    {
        $this->server = $this->listen -> addlistener(
            $this->config['server']['host'],
            $this->config['server']['port'],
            $this->config['server']['sockType']
        );
        $this->listen -> set($this->config['listen']['SwConfig']);

        $event = new \iflow\Swoole\netPenetrate\Server\event\server($this);
        $this->on($this->server, $event);
        $this->server -> set($this->config['server']['SwConfig']);
        return $this;
    }

    public function startTunnel(): static
    {
        $this->tunnel = $this->listen -> addlistener(
            $this->config['tunnel']['host'],
            $this->config['tunnel']['port'],
            $this->config['tunnel']['sockType']
        );

        $event = new tunnel($this);
        $this->on($this->tunnel, $event);

        $this->consumption(function () {
            if (!$this->tunnelChannel -> isEmpty()) {
                $data = $this->tunnelChannel -> pop();
                $over = false;
                foreach ($this->table as $tunnelFd => $localFd) {
                    if ($localFd['local_fd'] == $data['fd']) {
                        $over = true;
                        $this->listen->send($tunnelFd, $data['data']);
                        break;
                    }
                }
                if ($over === false) {
                    $this->tunnelChannel->push($data);
                    \Swoole\Coroutine::sleep(0.2);
                }
            }
        });
        return $this;
    }

    private function consumption(\Closure $closure)
    {
        Timer::tick(10, function () use ($closure) {
            $closure();
        });
    }
}