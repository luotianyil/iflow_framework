<?php


namespace iflow\annotation\lib\abstracts;


use iflow\annotation\lib\interfaces\annotationValueInterface;

abstract class annotationAbstract implements annotationValueInterface
{
    protected mixed $default = '';

    /**
     * 获取当前值
     * @param \ReflectionProperty|\ReflectionParameter $ref
     * @param $object
     * @param array $args
     * @return mixed
     * @throws \Error|\ReflectionException
     */
    public function getValue(\ReflectionProperty|\ReflectionParameter $ref, $object, array &$args = []): mixed
    {
        $refIsProperty = $ref instanceof \ReflectionProperty;
        try {
            if ($refIsProperty) {
                return $ref -> getValue($object);
            }
            $value = $args[$ref -> getPosition()];
            if (is_bool($value) || is_null($value) || is_numeric($value)) return $value;

            return $value ?: throw new \Exception('method miss params null');
        } catch (\Error|\Exception $exception) {
            if ($refIsProperty) {
                return $ref -> getDefaultValue() ?: $this->defaultIsClass();
            }
            return $this->getDefault();
        }
    }

    /**
     * 验证是否为类
     * @return mixed
     */
    protected function defaultIsClass(): mixed
    {
        if (is_string($this->default) && class_exists($this->default)) {
            $this->default = app() -> make($this->default);
        }
        return $this->default;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }
}