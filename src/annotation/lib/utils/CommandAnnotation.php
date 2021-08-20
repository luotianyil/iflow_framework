<?php


namespace iflow\annotation\lib\utils;


use iflow\App;
use ReflectionClass;

#[\Attribute]
class CommandAnnotation
{

    /**
     * 定义控制行注解
     * Command constructor.
     * @param string $command
     */
    public function __construct(
        protected string $command
    ) {}

    /**
     * 验证类 并将类写入配置
     * @param App $app
     * @param ReflectionClass $annotationClass
     * @throws \ReflectionException
     */
    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $command = $annotationClass -> newInstance();
        if (!$command instanceof \iflow\console\lib\Command)
            throw new \RuntimeException($command::class . ' instanceof Command fail');

        config([
            $this->command => $command::class
        ], 'command');
    }
}