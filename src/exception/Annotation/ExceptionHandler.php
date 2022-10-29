<?php

namespace iflow\exception\Annotation;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_METHOD)]
class ExceptionHandler extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
    }
}