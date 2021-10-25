<?php

namespace iflow\event;

use \iflow\App;
use iflow\event\lib\Abstracts\SubjectAbstract;
use \iflow\Utils\ArrayTools;

class Event
{

    protected App $app;
    protected ArrayTools $arrayTools;

    protected array $events = [];

    public function initializer(App $app)
    {
        $this->app = $app;
        foreach (config('event') ?: [] as $name => $event) {
            $event = new $event;
            if (!$event instanceof SubjectAbstract) {
                throw new \RuntimeException($event::class . ' instanceof SubjectAbstract fail');
            }
            $this->bind($name, $event);
        }
        $this->arrayTools = new ArrayTools($this->events);
    }

    /**
     * 绑定事件
     * @param string $name
     * @param SubjectAbstract|\Closure $event
     * @return Event
     */
    public function bind(string $name, SubjectAbstract|\Closure $event): static
    {
        $this->events[$name] = $event;
        return $this;
    }

    /**
     * 触发事件
     * @param string $event
     * @param array $args
     * @return mixed
     */
    public function trigger(string $event, array $args = []): mixed
    {
        if (empty($this->events[$event])) {
            throw new \Error("event error: ${event} not exists");
        }
        $event = $this->events[$event];
        if ($event instanceof \Closure) {
            return $event(...$args);
        }
        return $event -> trigger(...$args);
    }

}