<?php

namespace iflow\exception\Annotation;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\initializer\Error;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class ConfigureException extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Created;

    public function __construct(protected string $clazz = \Throwable::class, protected array $args = []) {
    }

    /**
     * ConfigureException
     * @desc 自定义异常接管类 可处理指定接管异常
     * @param Reflector $reflector
     * @param $args
     * @return mixed
     * @throws InvokeClassException
     */
    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        return app(Error::class) -> setTakeoverConfigure(
            $this->clazz, $reflector -> getName(), $this->args
        );
    }
}
