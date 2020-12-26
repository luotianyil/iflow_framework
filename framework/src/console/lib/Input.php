<?php


namespace iflow\console\lib;


use iflow\App;

class Input
{

    public function __construct(protected array $argv = [])
    {
        $this->argv = $argv ?: $_SERVER['argv'];
    }

    public function getUserCommand(): array
    {
        return $this->argv;
    }

    // 解析 用户 输入指令并执行
    public function parsingInputCommand(array $command, App $app)
    {
        if (count($this->argv) < 2) {
            return false;
        }

        $userCommand = explode('-', $this->argv[1]);
        // 用户指令
        $methods = count($userCommand) < 2 ? $userCommand[0] : $userCommand[1];

        foreach ($command as $key => $value) {
            // 配置指令
            $key_command = explode('-', $key);
            $commandClass = '';

            if (count($key_command) < 2) {
                if ($key === $methods) $commandClass = $value;
            } else {
                if ($key_command[1] === $methods) $commandClass = $value;
            }

            if ($commandClass !== '') {
                if (!class_exists($commandClass)) throw new \Error("class {$commandClass} not exists");
                else {
                    $commandClass = $app -> invokeClass($commandClass, $this->argv);
                    $app -> invoke([$commandClass, 'handle']);
                }
            }
        }
        return false;
    }

}