<?php


namespace iflow\aop\Annotation;


use Attribute;
use iflow\aop\Aop;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class Aspect extends AnnotationAbstract {

    public function __construct(protected array $aspectArray = []) {}

    /**
     * 添加切面
     * @param Reflector $reflector
     * @param $args
     * @return mixed
     * @throws InvokeClassException
     */
    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        app(Aop::class) -> addAspect($reflector->getName(), $this->aspectArray);
        return true;
    }
}
