<?php


namespace iflow\Swoole\GraphQL\Annotation\Type;

use Attribute;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Swoole\GraphQL\Types\Enum\typeEnum;
use Reflector;

#[Attribute(Attribute::TARGET_PROPERTY)]
class typeEnumAnnotation extends typeEnum {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function process(Reflector $reflector, &$args): typeEnum {
        // TODO: Implement process() method.
        return $this->initConfig($reflector);
    }

    /**
     * 初始配置
     * @param Reflector $annotationClass
     * @return typeEnum
     */
    public function initConfig(Reflector $annotationClass): typeEnum {
        $this->enumType = config('graphql@'.$this->name) ?: null;
        if ($this->enumType !== null) return $this;

        $properties = $annotationClass->getProperties();
        foreach ($properties as $property) {
            $this->values[$property -> getName()] = $property -> getDefaultValue();
        }
        config([ $this->name => $this -> getTypeObject() ], 'graphql');
        return $this;
    }
}