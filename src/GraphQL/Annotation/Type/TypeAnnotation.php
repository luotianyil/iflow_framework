<?php


namespace iflow\GraphQL\Annotation\Type;


use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\GraphQL\Types\Query;
use iflow\GraphQL\Types\TypeFields;
use iflow\GraphQL\Types\TypeName;
use Reflector;

/**
 * GraphQL 字段类型注解
 * Class Type
 * @package iflow\Swoole\GraphQL\Annotation\Type
 */
#[Attribute(Attribute::TARGET_CLASS)]
class TypeAnnotation extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    protected Reflector $annotationClass;

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
        $this->types = new TypeName($this->typeName, $this->typeDescription, valid_closure($this->resolveType));
        $this->typeFields = new TypeFields();
    }

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        $this->annotationClass = $reflector;

        if (config('graphql@'.$this->typeName)) return $this->getTypeObject();

        // 设置TypeName
        if ($this->typeName === '') {
            $this->typeName = $reflector->getName();
            $this->types->setTypeName($this->typeName);
        }

        $properties = $reflector->getProperties();

        // 获取实例化对象
        $object = $this->getObject($args);

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

        config([$this->typeName => new Query($this->types, $this->typeFields)], 'graphql');
        return $this;
    }

    /**
     * 执行参数注解
     * @param \ReflectionProperty $reflectionProperty
     * @param string $name
     * @param $object
     * @return mixed
     */
    protected function properAttributes(\ReflectionProperty $reflectionProperty, string $name, $object): mixed {
        $attribute = $reflectionProperty->getAttributes($name)[0] ?? null;

        $args = [ $object ];
        return $attribute?->newInstance()?->process($reflectionProperty, $args);
    }


    public function getTypeObject() {
        return config('graphql@'.$this->typeName) -> getTypeObject();
    }
}