<?php


namespace iflow\console\lib;


/**
 * 命令行参数
 * Trait Argument
 * @package iflow\console\lib
 */
trait Argument
{

    // 指令参数
    protected array $arguments = [];
    protected array $instruction = [];

    /**
     * 获取指令参数
     * @param string $name
     * @param string $default
     * @return mixed|string
     */
    public function getArgument(string $name, string $default = '')
    {
        if (!str_starts_with($name, '-')) {
            $name = "-${name}";
        }
        $instructionKey = array_search($name, $this->instruction);
        if (!$instructionKey) {
            return $default;
        }

        $instructionValue = $this->instruction[$instructionKey + 1] ?? null;
        if (!$instructionValue || str_starts_with($instructionValue, '-')) {
            return $default;
        }
        return $instructionValue;
    }

    /**
     * 初始化参数
     * @param Input $input
     */
    protected function parserArgumentInstruction(Input $input) {
        $instruction = $input -> getUserCommand();
        $this->instruction = array_slice($instruction, 1);
    }
}