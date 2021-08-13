<?php


namespace iflow\Swoole\GraphQL\Annotation\Type;


use iflow\App;
use iflow\Swoole\GraphQL\Annotation\Type\lib\ArgsType;
use iflow\Swoole\GraphQL\Annotation\Type\lib\fieldType;
use iflow\Swoole\GraphQL\Annotation\Type\lib\resolveType;
use iflow\Swoole\GraphQL\Types\Query;
use iflow\Swoole\GraphQL\Types\typeFields;
use iflow\Swoole\GraphQL\Types\typeName;
use ReflectionClass;

/**
 * GraphQL 字段类型注解
 * Class Type
 * @package iflow\Swoole\GraphQL\Annotation\Type
 */
#[\Attribute]
class TypeAnnotation
{

    protected App $app;
    protected ReflectionClass $annotationClass;

    protected array $methodAttribute = [
        fieldType::class,
        ArgsType::class,
        resolveType::class
    ];

    protected typeName $types;
    protected typeFields $typeFields;

    public function __construct(
        protected string $typeName,
        protected string $typeDescription = '',
        protected string $resolveType = '',
    ) {
        $this->types = new typeName(
            $this->typeName, $this->typeDescription,
            valid_closure($this->resolveType)
        );
        $this->typeFields = new typeFields();
    }

    /**
     * 初始化当前 GraphQL 注解
     * @param App $app
     * @param ReflectionClass $annotationClass
     * @throws \ReflectionException
     */
    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $this->app = $app;
        $this->annotationClass = $annotationClass;

        // 设置TypeName
        if ($this->typeName === '') {
            $this->typeName = $annotationClass->getName();
            $this->types->setTypeName($this->typeName);
        }

        $properties = $annotationClass->getProperties();

        // 实例化对象
        $object = $annotationClass->newInstance();

        foreach ($properties as $property) {
            $field = [];
            foreach ($this->methodAttribute as $attrName) {
                array_push($field, ...($this->properAttributes($property, $attrName, $object) ?: []));
            }

            try {
                if (!empty($field)) $this->typeFields -> setFields(...$field);
            } catch (\Throwable) {
                throw new \Exception('fieldType/ArgsType/resolveType Prohibited to be empty');
            }

        }

        config([
            $this->typeName => new Query($this->types, $this->typeFields)
        ], 'graphql');
    }

    /**
     * 执行参数注解
     * @param \ReflectionProperty $reflectionProperty
     * @param string $name
     * @param $object
     * @return false|mixed|null
     */
    protected function properAttributes(\ReflectionProperty $reflectionProperty, string $name, $object)
    {
        $attribute = $reflectionProperty->getAttributes($name)[0] ?? null;
        if ($attribute) {
            return call_user_func([$attribute->newInstance(), 'handle'], ...[$reflectionProperty, $object]);
        }
        return null;
    }
}