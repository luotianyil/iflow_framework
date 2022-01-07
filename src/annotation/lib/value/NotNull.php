<?php


namespace iflow\annotation\lib\value;

use iflow\annotation\lib\abstracts\annotationAbstract;
use iflow\annotation\lib\value\Exception\valueException;

#[\Attribute]
class NotNull extends annotationAbstract
{
    public function __construct(
      private mixed $value = "",
      private string $error = ""
    ) {}

    /**
     * @param \ReflectionProperty|\ReflectionParameter $ref
     * @param $object
     * @param array $args
     * @return bool
     * @throws \ReflectionException
     */
    public function handle(\ReflectionProperty|\ReflectionParameter $ref, $object, array &$args = []): bool
    {
        try {
            // 获取初始化值
            $value = $this->getValue($ref, $object, $args);

            $type = app() -> getParameterType($ref);

            if (in_array('array', $type) && empty($type)) $this->throwError($ref);
            if (!is_null($value) && $value !== '') return true;
            $this->throwError($ref);
        } catch (\Error) {
            $this->throwError($ref);
        }
    }

    protected function throwError($ref) {
        throw new valueException(message() -> parameter_error($this->error ?: "{$ref -> getName()} required"));
    }
}