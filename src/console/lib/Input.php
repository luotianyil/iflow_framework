<?php


namespace iflow\console\lib;


use iflow\App;
use iflow\console\Console;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;

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

    /**
     * 解析 用户 输入指令并执行
     * @param array $command
     * @param Console $console
     * @throws \Throwable
     * @return mixed
     */
    public function parsingInputCommand(array $command, Console $console)
    {
        if (count($this->argv) < 2) {
            return $this->invokeClass($console, Help::class, $command);
        }

        $userCommand = explode('-', $this->argv[1]);
        // 用户指令
        $methods = count($userCommand) < 2 ? $userCommand[0] : $userCommand[1];

        // 验证 指令是否存在
        foreach ($command as $key => $value) {

            // 直接验证是否存在
            if ($methods === $key) {
                return $this->invokeClass($console, $value, $userCommand);
            }

            // 配置指令
            $key_command = explode('-', $key);
            $commandClass = '';

            // 检测是否有子级指令
            if (count($key_command) < 2) continue;

            $key_command_start = explode('|', str_replace(['<', '>'], '', $key_command[0]));

            if ($methods === $key_command[1] && in_array($userCommand[0], $key_command_start)) $commandClass = $value;
            if ($commandClass !== '') {
                if (!class_exists($commandClass)) throw new \Error("class {$commandClass} not exists");
                else {
                    return $this->invokeClass($console, $commandClass, $userCommand);
                }
            }
        }

        return $this->invokeClass($console, Help::class, $userCommand);
    }

    /**
     * @param Console $console
     * @param $commandClass
     * @param $userCommand
     * @return mixed
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     */
    public function invokeClass(Console $console, $commandClass, $userCommand): mixed {
        $commandClass = $console -> app -> invokeClass($commandClass, $this->argv);
        $commandClass -> setApp($console -> app)
            -> setConsole($console)
            -> setArgument();
        return $console -> app -> invoke([$commandClass, 'handle'], [$userCommand]);
    }

}