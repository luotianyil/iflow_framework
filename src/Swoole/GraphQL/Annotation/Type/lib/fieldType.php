<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;


use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Swoole\GraphQL\Annotation\Type\lib\utils\Types;
use Reflector;

#[Attribute(Attribute::TARGET_PROPERTY)]
class fieldType extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function __construct(
        protected string $fieldName,
        protected string|array $type,
        protected string $description = ''
    ) {}

    public function process(Reflector $reflector, &$args): array {
        // TODO: Implement process() method.
        return [
            $this->fieldName,
            (new Types()) -> getType($this->type),
            $this->description
        ];
    }
}