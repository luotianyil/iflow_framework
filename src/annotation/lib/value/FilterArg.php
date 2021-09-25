<?php


namespace iflow\annotation\lib\value;


use iflow\annotation\lib\abstracts\annotationAbstract;

#[\Attribute]
class FilterArg extends annotationAbstract
{
    public function __construct(
        protected mixed $called,
        protected array $calledParams = [],
        protected string $name = ''
    ) {}

    /**
     * 过滤参数
     * @param \ReflectionProperty|\ReflectionParameter $ref
     * @param $object
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle(\ReflectionProperty|\ReflectionParameter $ref, $object, array &$args = []): mixed
    {
        // TODO: Implement handle() method.
        $value = $this->getValue($ref, $object, $args);
        if ($ref instanceof \ReflectionProperty) {
            $ref -> setValue($object, $this->called($value));
            return $ref -> getValue($object);
        } else {
            $index = $ref -> getPosition();
            return $args[$index] = $this->called($args[$index] ?: '');
        }
    }

    protected function called($value)
    {
        return valid_closure($this->called, [
            $value, ...$this -> calledParams
        ])();
    }
}