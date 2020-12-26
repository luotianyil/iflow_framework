<?php


namespace iflow\console;


use iflow\App;
use iflow\console\lib\Help;
use iflow\console\lib\Input;
use iflow\console\lib\outPut;
use iflow\Swoole\Services\Services;

class Console
{
    protected App $app;

    protected Input $input;
    protected outPut $outPut;

    public array $command = [
        '<start|stop>-tcp',
        '<start|stop>-udp',
        '<start|stop>-service' => Services::class,
        'help' => Help::class
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
        $this->input -> parsingInputCommand($this->command, $this->app);
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