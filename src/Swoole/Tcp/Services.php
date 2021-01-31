<?php


namespace iflow\Swoole\Tcp;


use iflow\Swoole\Tcp\lib\tcpService;

class Services extends \iflow\Swoole\Services
{

    protected array $initializers = [
        tcpService::class
    ];

    public function run()
    {
        $this->userEvent[2] = empty($this->userEvent[2]) ? 'server' : ($this->userEvent[2] === 'server' ? 'server' : 'client');
        if ($this->userEvent[2] !== 'client') {
            $this->initializer();
        }
    }

}