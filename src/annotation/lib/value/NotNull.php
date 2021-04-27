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

    public function handle(\ReflectionProperty $ref, $object)
    {
        try {
            // 获取数值 如果未初始化 抛出异常
            return $ref -> getValue($object);
        } catch (\Error) {
            throw (new valueException()) -> setError(message() -> parameter_error($this->error));
        }
    }
}