<?php


namespace iflow\Swoole\GraphQL\Annotation\Type;

use iflow\App;
use iflow\Swoole\GraphQL\Types\Enum\typeEnum;
use ReflectionClass;

#[\Attribute]
class typeEnumAnnotation extends typeEnum
{
    protected App $app;
    public function __make(App $app, ReflectionClass $annotationClass): typeEnum
    {
        return $this->initConfig($annotationClass);
    }

    /**
     * 初始配置
     * @param ReflectionClass $annotationClass
     * @return typeEnum
     */
    public function initConfig(ReflectionClass $annotationClass): typeEnum
    {
        $this->enumType = config('graphql@'.$this->name) ?: null;
        if ($this->enumType !== null) return $this;

        $properties = $annotationClass->getProperties();
        foreach ($properties as $property) {
            $this->values[$property -> getName()] = $property -> getDefaultValue();
        }
        config([
            $this->name => $this -> getTypeObject()
        ], 'graphql');
        return $this;
    }
}