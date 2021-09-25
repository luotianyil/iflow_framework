<?php


namespace iflow\annotation\lib\value;

use iflow\annotation\lib\abstracts\annotationAbstract;

#[\Attribute]
class Value extends annotationAbstract
{
    public function __construct(
        protected mixed $default = "",
        protected string $desc = ""
    ) {
    }

    /**
     * 初始化数值
     * @param \ReflectionProperty|\ReflectionParameter $ref
     * @param $object
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle(\ReflectionProperty|\ReflectionParameter $ref, $object, array &$args = [])
    {
        $value = $this->getValue($ref, $object, $args);
        if ($ref instanceof \ReflectionProperty) {
            $ref -> setValue($object, $value);
        } else {
            $args[$ref -> getPosition()] = $value;
        }
        return $value;
    }
}