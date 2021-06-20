<?php


namespace iflow\annotation\lib\value;

use iflow\annotation\lib\value\Exception\valueException;

#[\Attribute]
class NotNull
{
    public function __construct(
      private mixed $value = "",
      private string $error = ""
    ) {}

    /**
     * @param \ReflectionProperty $ref
     * @param $object
     * @throws valueException
     */
    public function handle(\ReflectionProperty $ref, $object)
    {
        try {
            // 获取数值 如果未初始化 抛出异常
            $value = $ref -> getValue($object);
            if (!is_bool($value) && !$value) $this->throwError($ref);
        } catch (\Error) {
            $this->throwError($ref);
        }
    }

    private function throwError($ref)
    {
        throw new valueException(message() -> parameter_error($this->error ?: "{$ref -> getName()} required"));
    }
}