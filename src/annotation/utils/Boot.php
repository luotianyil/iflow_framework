<?php


namespace iflow\annotation\utils;


use Attribute;
use iflow\annotation\interfaces\BootInterface;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class Boot extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    /**
     * Boot constructor.
     * 启动引导类 注解
     * 使用该注解时
     * 在框架启动前会将当前类 自动注入容器
     * @param string $bootName
     * @param array $options
     */
    public function __construct(
        protected string $bootName = '', protected array $options = []
    ) {}

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        $boot = app() -> GenerateClassParameters($reflector, $reflector -> newInstance());
        if (!$boot instanceof BootInterface)
            throw new \RuntimeException($boot::class . ' instanceof BootInterface fail');
        return app() -> register($reflector -> getName(), $boot -> boot());
    }
}
