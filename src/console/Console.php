<?php


namespace iflow\console;


use iflow\App;
use iflow\command\buildPhar;
use iflow\command\cmdInstruction;
use iflow\command\http;
use iflow\command\install;
use iflow\command\workMan;
use iflow\console\Adapter\Input;
use iflow\console\Adapter\WriteConsole;
use iflow\http\HttpServer;
use iflow\swoole\implement\Services\Kafka\Services;
use iflow\swoole\ServicesCommand;

class Console
{
    public App $app;

    public Input $input;

    public WriteConsole $writeConsole;

    public array $command = [
        '<start|stop|reload>-service' => ServicesCommand::class,
        '<start|stop|reload>-websocket' => ServicesCommand::class,
        'start-workerMan' => workMan::class,
        '<start|stop|reload>-<tcp|udp|mqtt|rpc>-<client|server>' => ServicesCommand::class,
        'start-dht-services' => ServicesCommand::class,
        'start-kafka-consumer' => Services::class,
//        'start-proxy-<client|server>' => netPenetrate::class,
        'start' => http::class,
        'start-dev' => HttpServer::class,
        'install' => install::class,
        'build' => buildPhar::class,
        'shell' => cmdInstruction::class
    ];

    protected array $userCommand = [];

    /**
     * @param App $app
     * @return void
     * @throws \Throwable
     */
    public function initializer(App $app): void {
        $this->app = $app;
        $this->input = new Input();
        $this->writeConsole = new WriteConsole($this->openOutputStream());
        // 获取用户输入
        $this->userCommand = $this->input -> getUserCommand();

        $this -> exec();
    }

    /**
     * @return void
     * @throws \Throwable
     */
    protected function exec() {
        $this->getCommand()
            -> input
            -> parsingInputCommand($this->command, $this);
    }

    protected function getCommand(): static {
        $this->command = array_merge(config('command'), $this->command);
        return $this;
    }

    public function outWrite($content = '') {
        $this->writeConsole -> write($content) -> outPutWrite();
    }

    protected function isRuningOS400(): bool {
        $checks = [
            function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            PHP_OS,
        ];
        return false !== stripos(implode(';', $checks), 'OS400');
    }

    /**
     * @return bool
     */
    protected function hasConsoleWrite(): bool {
        return false === $this->isRuningOS400();
    }

    /**
     * @return false|resource
     */
    protected function openOutputStream() {
        if (!$this->hasConsoleWrite()) {
            return fopen('php://output', 'w');
        }
        return @fopen('php://stdout', 'w') ?: fopen('php://output', 'w');
    }
    
}