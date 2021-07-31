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

    /**
     * 初始化注解入口类
     * @param App $app
     */
    public function initializer(App $app)
    {
        $this->app = $app;
        $this->loadAnnotations($this->app -> appRunClass);
    }

    /**
     * 执行 App 入口类注解
     * @param ReflectionClass $annotationClass
     */
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