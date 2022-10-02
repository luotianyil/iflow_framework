<?php


namespace iflow\aop\Annotation;


use Attribute;
use iflow\aop\Aop;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class Aspect extends AnnotationAbstract {

    public function __construct(protected array $aspectArray = []) {}

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        return app(Aop::class) -> addAspect($reflector->getName(), $this->aspectArray);
    }
}
