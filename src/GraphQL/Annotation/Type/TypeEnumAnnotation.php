<?php


namespace iflow\GraphQL\Annotation\Type;

use Attribute;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\GraphQL\Types\Enum\TypeEnum;
use Reflector;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS)]
class TypeEnumAnnotation extends TypeEnum {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function process(Reflector $reflector, &$args): TypeEnum {
        // TODO: Implement process() method.
        return $this->initConfig($reflector);
    }

    /**
     * 初始配置
     * @param Reflector $annotationClass
     * @return TypeEnum
     */
    public function initConfig(Reflector $annotationClass): TypeEnum {
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