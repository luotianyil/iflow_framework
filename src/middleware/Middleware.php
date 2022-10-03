<?php


namespace iflow\middleware;


use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class Middleware extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    protected array $middleware = [];

    public function __construct(protected array $params = []) {}

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        $this->middleware = config('middleware');
        $classes = $reflector -> getName();
        if (!in_array([ $classes, $this->params ], $this->middleware)) {
            $this->middleware[] = [$classes, $this->params];
            config($this->middleware, 'middleware');
        }
        return $classes;
    }
}