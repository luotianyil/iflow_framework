<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;


use GraphQL\Type\Definition\Type;

#[\Attribute]
class fieldType
{
    public function __construct(
        protected string $fieldName,
        protected string|Type $type,
        protected string $description = ''
    ) {}

    public function handle(\ReflectionProperty $reflectionProperty, $object)
    {
        $this->type = call_user_func([Type::class, $this->type]);
        return [
            $this->fieldName,
            $this->type,
            $this->description
        ];
    }
}