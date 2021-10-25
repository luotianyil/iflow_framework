<?php


namespace iflow\annotation\lib\utils;


use iflow\App;
use iflow\event\Event;
use iflow\event\lib\Abstracts\SubjectAbstract;
use ReflectionClass;

#[\Attribute]
class EventAnnotation
{
    public function __construct(
        protected string $eventName
    ) {}


    /**
     * 绑定事件
     * @param App $app
     * @param ReflectionClass $annotationClass
     */
    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $event = $app -> make($annotationClass -> getName());
        if (!$event instanceof SubjectAbstract) {
            throw new \RuntimeException($event::class . ' instanceof SubjectAbstract fail');
        }
        $app -> make(Event::class) -> bind($this->eventName, $event);
    }
}