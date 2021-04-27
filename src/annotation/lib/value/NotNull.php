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

    public function handle($value = null)
    {
        if (!$value) throw (new valueException()) -> setError($this->error);
    }
}