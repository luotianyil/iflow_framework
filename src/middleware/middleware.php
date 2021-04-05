<?php


namespace iflow\middleware;


use iflow\App;
use ReflectionClass;

#[\Attribute]
class middleware
{

    protected array $middleware = [];

    public function __construct(protected array $params = []) {}

    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $this->middleware = config('middleware');
        $classes = $annotationClass -> getName();
        if (!in_array($classes, $this->middleware)) {
            $this->middleware[] = [$classes, $this->params];
            config($this->middleware, 'middleware');
        }
    }

}