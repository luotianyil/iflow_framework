<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;


use GraphQL\Type\Definition\Type;

#[\Attribute]
class ArgsType
{
    public function __construct(protected array $args = []) {}

    public function handle(\ReflectionProperty $reflectionProperty, $object): array
    {
        foreach ($this->args as $argKey => $value) {
            $this->args[$argKey]['type'] = call_user_func([Type::class, $this->args[$argKey]['type'] ?? 'string']);
        }
        return [
            $this->getArgs()
        ];
    }


    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
}