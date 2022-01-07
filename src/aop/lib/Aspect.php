<?php


namespace iflow\aop\lib;


use iflow\aop\Aop;
use iflow\App;
use ReflectionClass;

#[\Attribute]
class Aspect
{

    public function __construct(
       protected array $aspectArray = []
    ) {}


    public function __make(App $app, ReflectionClass $annotationClass) {
        app() -> make(Aop::class) -> addAspect($annotationClass->getName(), $this->aspectArray);
    }
}
