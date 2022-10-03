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
    public function parsingInputCommand(array $command, Console $console) {
        if (count($this->argv) < 2) {
            return $this->invokeClass($console, HasCommand::class, $command);
        }

        $userCommand = $this->argv[1];
        $commandClass = HasCommand::class;

        if (!array_key_exists($userCommand, $command)) {
            $userCommand = explode('-', $this->argv[1]);

            // 验证 指令是否存在
            foreach ($command as $commandKey => $value) {

                $commandKey = explode('-', $commandKey) ?: [];

                foreach ($commandKey as $index => $commandChildren) {
                    $key_command_start = explode('|', str_replace(['<', '>'], '', $commandChildren[0]));
                    $userCommandIndex = array_shift($userCommand);

                    if (!in_array($userCommandIndex, $key_command_start)) break;

                    if (count($userCommand) === 0 && count($commandKey) - 1 === $index) {
                        $commandClass = $value;
                    }
                }
            }
        } else {
            $commandClass = $command[$userCommand];
        }

        $userCommand = explode('-', $this->argv[1]);
        if (!class_exists($commandClass)) throw new \Error("class $commandClass not exists");
        else {
            return $this->invokeClass($console, $commandClass, $userCommand);
        }
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