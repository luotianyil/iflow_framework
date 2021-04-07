<?php

namespace iflow\event;

use \iflow\App;

class Event
{

    protected App $app;
    protected \iflow\Utils\ArrayTools $arrayTools;

    protected array $events = [];

    public function initializer(App $app)
    {
        $this->app = $app;
        $this->bindEvent(config('event'));
        $this->arrayTools = new \iflow\Utils\ArrayTools($this->events);
    }

    public function bindEvent(array $events = [])
    {
        $this->events = array_merge($events, $this->events);
    }

    public function trigger(string $event, array $args = [])
    {
        if ($this->arrayTools -> offsetExists($event)) {
            $event = $this->app -> make($this->arrayTools -> offsetGet($event), isNew: true);
            return $this->app -> invokeMethod([$event, 'handle'], $args);
        } else {
            throw new \Error("event error: ${event} not exists");
        }
    }

}