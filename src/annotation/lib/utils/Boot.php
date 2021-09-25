<?php


namespace iflow\annotation\lib\utils;


use iflow\annotation\lib\interfaces\bootInterface;
use iflow\App;
use ReflectionClass;

#[\Attribute]
class Boot
{

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

    /**
     * @param App $app
     * @param ReflectionClass $annotationClass
     * @throws \ReflectionException
     */
    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $boot = $annotationClass -> newInstance();
        if (!$boot instanceof bootInterface)
            throw new \RuntimeException($boot::class . ' instanceof bootInterface fail');

        app() -> instance($boot::class, $boot -> boot());
    }
}