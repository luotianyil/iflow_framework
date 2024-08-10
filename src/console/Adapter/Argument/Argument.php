<?php

namespace iflow\console\Adapter\Argument;

use iflow\console\Adapter\Input;

trait Argument {

    // 指令参数
    protected array $arguments = [];

    protected array $instruction = [];

    /**
     * 获取指令参数
     * @param string $name
     * @param string $default
     * @return mixed|string
     */
    public function getArgument(string $name, string $default = ''): mixed {
        $name = $this->getArgumentsKey($name);

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
     * @param Input|array $input
     * @param int $offset
     * @return Argument
     */
    protected function parserArgumentInstruction(Input|array $input, int $offset = 1): static {
        $instruction = is_array($input) ? $input : $input -> getUserCommand();
        $this->instruction = array_slice($instruction, $offset);
        return $this;
    }

    protected function getArgumentsKey(string $name): string {
        if (!str_starts_with($name, '-')) {
            $name = "-{$name}";
        }

        return $name;
    }

    /**
     * 验证参数是否存在
     * @param string $name
     * @return bool
     */
    public function hasArgument(string $name): bool {
        $name = $this->getArgumentsKey($name);
        return in_array($name, $this->instruction);
    }
}