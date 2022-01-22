<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;


use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_PROPERTY)]
class resolveType extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function __construct(protected string|\Closure $resolve) {}

    public function process(Reflector $reflector, &$args): array {
        // TODO: Implement process() method.
        $object = $this->getObject($args);
        return [
            valid_closure($this->resolve, $object ? [ $object ]: [])
        ];
    }

}