<?php

namespace iflow\swoole\implement\Client\Rpc\Services;

use iflow\swoole\implement\Client\implement\abstracts\Client;
use iflow\swoole\implement\Commounity\Rpc\Request\Routers\CheckRequestRouter;
use iflow\swoole\implement\Server\Rpc\Parsers\Event;
use iflow\Utils\Tools\Timer;

class Subscription extends Client {

    /**
     * 监听信息
     * @return bool
     */
    protected function wait(): bool {
        // TODO: Implement wait() method.
        if (!$this->client -> isConnected()) return false;

        $this->sendRegisterBody();

        while ($this->client -> isConnected()) {
            $write = $error = [];
            $read = [ $this->client ];
            $n = swoole_client_select($read, $write, $error);
            if ($n > 0) {
                $this->onPacket($this->client -> recv());
            }
        }

        return false;
    }

    /**
     * 发送注册信息
     * @return mixed
     */
    protected function sendRegisterBody(): mixed {

        $listener_default_name = $this->services -> getServicesCommand() -> config -> get('listener_default_name');

        return $this->send([
            'name' => $this->services -> getClientNames(),
            'event' => 0,
            'host' => [
                'http/ws' => $this->services -> getSwService() -> host . ':' . $this->services -> getSwService() -> port,
                'tpc' => $this->services -> getServicesCommand() -> config -> get('listeners@'.$listener_default_name)
            ]
        ]);
    }

    protected function onPacket(string|null $data = null): mixed {

        $data = $data === null ? $this->client -> recv() : $data;

        if (is_numeric($data) && intval($data) === Event::ping -> value) return $this->send([
            'event' => Event::pong -> value
        ]);

        $data = json_decode($data, true) ?: [];
        if (empty($data)) return false;

        return (new CheckRequestRouter()) -> init($this->client, 0, $data);
    }

    public function subscription() {
        $this -> Connection();
    }

}