<?php

namespace iflow\console\Adapter;

use iflow\console\Console;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;

class Input {

    public function __construct(protected array $argv = []) {
        $this->argv = $argv ?: $_SERVER['argv'];
    }

    public function getUserCommand(): array {
        return $this->argv;
    }

    /**
     * 解析 用户 输入指令并执行
     * @param array $command
     * @param Console $console
     * @throws \Throwable
     * @return mixed
     */
    public function parsingInputCommand(array $command, Console $console): mixed {
        if (count($this->argv) < 2) {
            return $this->invokeClass($console, HasCommand::class, $command);
        }

        $commandClass = $command[$this->argv[1]] ?? HasCommand::class;

        if (!array_key_exists($this->argv[1], $command) || $this->argv[1]) {

            // 验证 指令是否存在
            foreach ($command as $commandKey => $value) {

                $userCommand = explode('-', $this->argv[1]);
                $commandKey = explode('-', $commandKey) ?: [];

                if (count($userCommand) !== count($commandKey)) continue;

                foreach ($commandKey as $commandChildren) {
                    $key_command_start = explode('|', str_replace(['<', '>'], '', $commandChildren));
                    $userCommandIndex = array_shift($userCommand);

                    if (!in_array($userCommandIndex, $key_command_start)) break;

                    if (count($userCommand) === 0) $commandClass = $value;
                }
            }
        }

        if (!class_exists($commandClass)) throw new \Error("class $commandClass not exists");

        return $this->invokeClass($console, $commandClass, explode('-', $this->argv[1]));
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
        return $console -> app -> invoke([$commandClass, 'handle'], [ $userCommand ]);
    }

}