<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;

use iflow\Swoole\GraphQL\Annotation\Type\lib\utils\Types;

#[\Attribute]
class ArgsType
{
    public function __construct(protected array $args = []) {}

    public function handle(\ReflectionProperty $reflectionProperty, $object): array
    {
        foreach ($this->args as $argKey => $value) {
            $this->args[$argKey]['type'] = (new Types()) -> getType($this->args[$argKey]['type'] ?? 'string');
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