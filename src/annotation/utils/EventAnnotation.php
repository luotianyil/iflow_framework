<?php


namespace iflow\annotation\utils;


use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\event\Event;
use iflow\event\lib\Abstracts\SubjectAbstract;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class EventAnnotation extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    public function __construct(protected string $eventName) {}

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        $event = app() -> GenerateClassParameters($reflector, $reflector -> newInstance());
        if (!$event instanceof SubjectAbstract) {
            throw new \RuntimeException($event::class . ' instanceof SubjectAbstract fail');
        }
        return app() -> make(Event::class) -> bind($this->eventName, $event);
    }
}