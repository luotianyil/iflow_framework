<?php


namespace iflow\GraphQL\Annotation\Type;


use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\GraphQL\Annotation\Type\utils\Types;
use Reflector;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FieldType extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function __construct(
        protected string $fieldName,
        protected string|array $type,
        protected string $description = ''
    ) {}

    /**
     * @throws \ReflectionException
     */
    public function process(Reflector $reflector, &$args): array {
        // TODO: Implement process() method.
        return [
            $this->fieldName,
            (new Types()) -> getType($this->type),
            $this->description
        ];
    }
}