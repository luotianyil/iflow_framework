<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;


use iflow\Swoole\GraphQL\Annotation\Type\lib\utils\Types;

#[\Attribute]
class fieldType
{
    public function __construct(
        protected string $fieldName,
        protected string $type,
        protected string $description = ''
    ) {}

    public function handle(\ReflectionProperty $reflectionProperty, $object)
    {
        return [
            $this->fieldName,
            (new Types()) -> getType($this->type),
            $this->description
        ];
    }
}