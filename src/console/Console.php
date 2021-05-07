<?php


namespace iflow\console;


use iflow\App;
use iflow\command\buildPhar;
use iflow\command\dht;
use iflow\command\http;
use iflow\command\install;
use iflow\command\netPenetrate;
use iflow\console\lib\Help;
use iflow\console\lib\Input;
use iflow\console\lib\outPut;
use iflow\http\HttpServer;
use iflow\Swoole\Services\Services;

class Console
{
    public App $app;

    public Input $input;
    public outPut $outPut;

    public array $command = [
        '<start|stop|reload>-service' => Services::class,
        '<start|stop|reload>-tcp-<client|server>' => \iflow\Swoole\Tcp\Services::class,
        '<start|stop|reload>-udp-<client|server>' => \iflow\Swoole\Udp\Services::class,
        '<start|stop|reload>-mqtt-<client|server>' => \iflow\Swoole\MQTT\Services::class,
        '<start|stop|reload>-rpc-<client|server>' => \iflow\Swoole\Rpc\Services::class,
        'start-kafka-consumer' => \iflow\Swoole\Kafka\Services::class,
        'start-dht' => dht::class,
        'start-proxy-<client|server>' => netPenetrate::class,
        'start' => http::class,
        'start-dev' => HttpServer::class,
        'help' => Help::class,
        'install' => install::class,
        'build' => buildPhar::class
    ];

    protected array $userCommand = [];

    public function initializer(App $app)
    {
        $this->app = $app;
        $this->input = new Input();
        $this->outPut = new outPut($this->openOutputStream());
        // 获取用户输入
        $this->userCommand = $this->input -> getUserCommand();

        // 运行程序
        $this->run();
    }

    protected function run()
    {
        // 获取用户指定命令
        $this->getCommand();

        // 解析用户 指令
        if ($this->input -> parsingInputCommand($this->command, $this) === false) {
            $this->outPut -> writeLine('Unknown instruction');
        }
    }

    protected function getCommand(): static
    {
        $this->command = array_merge(config('command'), $this->command);
        return $this;
    }

    public function outWrite($content = '')
    {
        $this->outPut -> write($content) -> outPutWrite();
    }

    protected function isRuningOS400(): bool
    {
        $checks = [
            function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            PHP_OS,
        ];
        return false !== stripos(implode(';', $checks), 'OS400');
    }

    protected function hasConsoleWrite(): bool
    {
        return false === $this->isRuningOS400();
    }

    protected function openOutputStream()
    {
        if (!$this->hasConsoleWrite()) {
            return fopen('php://output', 'w');
        }
        return @fopen('php://stdout', 'w') ?: fopen('php://output', 'w');
    }
    
}