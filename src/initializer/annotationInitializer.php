<?php


namespace iflow\initializer;

// 注解实例化类
use iflow\App;
use ReflectionClass;

class annotationInitializer
{

    protected App $app;

    // 类全部注解
    protected array $annotations = [];

    // 实例化 入口类 全部注解
    public function initializer(App $app)
    {
        $this->app = $app;
        $this->loadAnnotations($this->app -> appRunClass);
    }

    public function loadAnnotations(ReflectionClass $annotationClass)
    {
        $annotations = $annotationClass -> getAttributes();
        foreach ($annotations as $key) {
            $annotation = $key -> newInstance();
            if (method_exists($annotation, '__make')) {
                call_user_func([$annotation, '__make'], ...[$this->app, $annotationClass]);
            }
        }
    }
}