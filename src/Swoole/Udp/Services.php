<?php


namespace iflow\Swoole\Udp;

use iflow\Swoole\Udp\lib\udpService;

class Services extends \iflow\Swoole\Services
{
    protected array $initializers = [
        udpService::class
    ];

    public function run()
    {
        $this->userEvent[2] = empty($this->userEvent[2]) ? 'service' : ($this->userEvent[2] === 'service' ? 'service' : 'client');
        if ($this->userEvent[2] !== 'client') {
            $this->initializer();
        }
    }
}