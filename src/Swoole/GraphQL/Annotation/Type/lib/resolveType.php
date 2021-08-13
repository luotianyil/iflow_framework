<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;


#[\Attribute]
class resolveType
{

    public function __construct(protected string|\Closure $resolve)
    {}

    public function handle(\ReflectionProperty $reflectionProperty, $object): array {
        return [
            valid_closure($this->resolve, $object ? [
                $object
            ]: [])
        ];
    }

}