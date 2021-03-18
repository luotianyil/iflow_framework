<?php


namespace iflow\Swoole\Rpc\lib;

use iflow\facade\Config;
use iflow\Swoole\Services\Http\lib\initializer;
use Swoole\Server;

class rpcReceive extends initializer
{

    public array $router;
    public int $fd;
    protected Server $server;
    protected ?array $clientList = [];
    protected mixed $config = [];

    public function handle(Server $server, $fd, $reactor_id, $data)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->config = config('swoole.rpc@server');
        $info = json_decode($data, true);

        $this->clientList = Config::getConfigFile(
            $this->config['clientList']['path'] . $this->config['clientList']['name']
        );

        if ($info) {
            if (isset($info['isClientConnection']) && isset($info['client_name'])) {
                return $this -> send(rpc($info['client_name'], $info['request_uri'], $info));
            }
            if ($this->addClient($info, $fd)) {
                $this -> send($info);
            }
            return true;
        }
        return true;
    }

    public function addClient($info, $fd)
    {
        if (!empty($info['initializer']) && $info['initializer'] == 1) {
            if (isset($this->clientList[$fd])) {
                return true;
            }

            if (empty($this->clientList['clientList'])) $this->clientList['clientList'] = [];
            $info['status'] = 1;
            $info['fd'] = $fd;
            $this->clientList['clientList'][$fd] = $info;

            Config::saveConfigFile($this->clientList,
                $this->config['clientList']['name'],
                $this->config['clientList']['path']
            );
            return true;
        }
        return false;
    }

    protected function send($response)
    {
        $this->server -> send($this->fd,
            match (!is_string($response)) {
                true => json_encode($response, JSON_UNESCAPED_UNICODE),
                default => $response
            }
        );
    }

    public function onClose($server, $fd)
    {}
}