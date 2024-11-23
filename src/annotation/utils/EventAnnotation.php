<?php


namespace iflow\annotation\utils;


use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\event\Event;
use iflow\event\Adapter\Abstracts\SubjectAbstract;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class EventAnnotation extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    /**
     * 事件绑定注解
     * @param string $eventName 事件名称
     */
    public function __construct(protected string $eventName) {}

    /**
     * @param Reflector $reflector
     * @param $args
     * @return mixed
     * @throws InvokeClassException
     */
    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        $event = app() -> GenerateClassParameters($reflector, $reflector -> newInstance());
        if (!$event instanceof SubjectAbstract) {
            throw new \RuntimeException($event::class . ' instanceof SubjectAbstract fail');
        }
        return app(Event::class) -> bind($this->eventName, $event);
    }
}