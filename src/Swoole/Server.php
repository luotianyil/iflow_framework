<?php


namespace iflow\Swoole;

use iflow\Swoole\lib\pid;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;

trait Server
{

    use Client;

    public function initializers(Services $services)
    {
        $this->services = $services;
        $this->eventType = strtolower($this->services -> userEvent[1]);
        $this -> setConfig() -> setPid() -> setParam() -> setOptions();
        if (strtolower($this->services -> userEvent[0]) !== 'stop') {
            if (isset($this->services -> userEvent[2])) {
                if (strtolower($this->services -> userEvent[2]) === 'client') $this -> initializerClient();
                else $this->initializerServer();
            } else {
                $this->initializerServer();
            }
        }
    }

    public function initializerServer()
    {
        if ($this->eventType === 'udp') {
            $this->param[] = SWOOLE_PROCESS;
            $this->param[] = SWOOLE_SOCK_UDP;
        }

        $serverClass = match ($this->eventType) {
          'service' => $this->services -> config['websocket']['enable'] ? WebSocketServer::class : HttpServer::class,
          default => \Swoole\Server::class
        };
        $this->server = new $serverClass(...$this->param);
        $this->services -> app -> instance($serverClass, $this->server);
        $this->server -> set($this->options);
    }


    public function setPid(): static
    {
        $this->pid = new pid(
            $this -> configs['pid_file'] ?? $this -> configs['swConfig']['pid_file']
        );
        return $this;
    }

    public function setConfig(): static
    {
        $this->configs = $this->services -> config;
        return $this;
    }

    public function setOptions(): static
    {
        $this->options = $this->configs;
        unset($this->options['host']);
        unset($this->options['options']);
        unset($this->options['websocket']);
        unset($this->options['Handle']);
        unset($this->options['mqttEvent']);
        return $this;
    }

    public function setParam(): static
    {
        $this->param = isset($this->configs['port']) ? [
            $this->configs['host'],
            $this->configs['port']
        ] : array_values($this->configs['host']);
        $this->Handle = $this->configs['Handle'];
        return $this;
    }

    public function getServer(): HttpServer|WebSocketServer|\Swoole\Server|\Swoole\Coroutine\Client
    {
        return $this->server;
    }
}