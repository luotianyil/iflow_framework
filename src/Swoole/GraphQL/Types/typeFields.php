<?php


namespace iflow\Swoole\GraphQL\Types;


use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
class typeFields extends AbstractType
{
    public function __construct(
        protected array $fields = []
    ) {}


    /**
     * 设置字段信息
     * @param string $fieldName
     * @param Type $type
     * @param string $description
     * @param array $args
     * @param \Closure|null $resolve
     * @return $this
     */
    public function setFields(
        string $fieldName,
        Type $type,
        string $description = '',
        array $args = [],
        ?\Closure $resolve = null
    ): static
    {
        $this->fields[$fieldName] = [
            'type' => $type,
            'description' => $description,
        ];

        if ($args) $this -> fields[$fieldName]['args'] = $args;
        $this -> fields[$fieldName]['resolve'] = $resolve === null ? $this->resolve() : $resolve;

        return $this;
    }

    public function resolve(): \Closure
    {
        return function ($current, $args, $context, ResolveInfo $info) {
            // TODO: 执行方法
        };
    }


    /**
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
    }
}