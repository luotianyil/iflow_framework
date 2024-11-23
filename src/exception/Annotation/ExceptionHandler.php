<?php

namespace iflow\exception\Annotation;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_METHOD)]
class ExceptionHandler extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function __construct(protected array|string $exceptionHandle, protected string|array $clazz = \Throwable::class, protected array $args = []) {
        $this->clazz = !is_array($this->clazz) ? [ $this->clazz ] : $this->clazz;

        // 设置异常接管处理类
        $this->exceptionHandle = is_array($this->exceptionHandle)
            ? $this->exceptionHandle : [ $this->exceptionHandle ];
        foreach ($this->exceptionHandle as &$handle) {
            $handle = [ $handle, $this->args ];
        }
    }

    public function process(Reflector $reflector, &$args): array {
        // TODO: Implement process() method.

        if (!in_array(\Throwable::class, $this->clazz) && !in_array($args[0], $this->clazz)) return [];
        return $this->exceptionHandle;
    }
}