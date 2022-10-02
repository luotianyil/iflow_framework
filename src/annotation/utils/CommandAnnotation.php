<?php


namespace iflow\annotation\utils;


use Attribute;
use iflow\console\Adapter\Command;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class CommandAnnotation extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    /**
     * 定义控制行注解
     * Command constructor.
     * @param string $command
     */
    public function __construct(
        protected string $command
    ) {}

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        if ($reflector -> getParentClass() -> getName() !== Command::class)
            throw new \RuntimeException($reflector -> getName() . ' instanceof Command fail');

        return config([ $this->command => $reflector -> getName() ], 'command');
    }
}