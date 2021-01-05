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
        if (isset($this->services -> userEvent[2])) {
            if (strtolower($this->services -> userEvent[2]) === 'client') $this -> initializerClient();
            else $this->initializerServer();
        } else {
            $this->initializerServer();
        }
    }

    public function initializerServer()
    {
        if ($this->eventType === 'udp') {
            $this->param[] = SWOOLE_PROCESS;
            $this->param[] = SWOOLE_SOCK_UDP;
        } elseif ($this->eventType === 'mqtt') {
            $this->param[] = SWOOLE_BASE;
        }

        $serverClass = match ($this->eventType) {
          'service' => $this->services -> config['websocket']['enable'] ? WebSocketServer::class : HttpServer::class,
          default => \Swoole\Server::class
        };

        $this->server = new $serverClass(...$this->param);
        $this->server -> set($this->options);
    }


    public function setPid(): static
    {
        $this->pid = new pid($this -> configs['pid_file']);
        return $this;
    }

    public function setConfig(): static
    {
        $this->configs = $this->services -> config[$this->eventType.'SwooleConfig'];
        $this->configs['pid_file'] = $this->configs['pid_file'].'.'.$this->eventType.'.pid';
        $this->configs['log_file'] = $this->configs['log_file'].'.'.$this->eventType.'.log';
        return $this;
    }

    public function setOptions(): static
    {

        $this->options = $this->configs;

        unset($this->options['serverHost']);
        unset($this->options['clientHost']);
        unset($this->options['Handle']);
        return $this;
    }

    public function setParam(): static
    {
        if ($this->eventType === 'service') {
            $this->param = [
                $this->services -> config['host'],
                $this->services -> config['port']
            ];
        } else {
            if (!empty($this->services -> userEvent[2])) {
                $this->param = array_values($this->configs[strtolower($this->services -> userEvent[2]) === 'client' ? 'clientHost' : 'serverHost']);
                $this->Handle = $this->configs['Handle'][strtolower($this->services -> userEvent[2]) === 'client' ? 'ClientHandle' : 'ServerHandle'];
            } else {
                $this->param = array_values($this->configs['serverHost']);
                $this->Handle = $this->configs['Handle']['ServerHandle'];
            }
        }
        return $this;
    }

    public function getServer(): HttpServer|WebSocketServer|\Swoole\Server|\Swoole\Coroutine\Client
    {
        return $this->server;
    }

}