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
        $this->bindEvent();
        $this->arrayTools = new \iflow\Utils\ArrayTools($this->events);
    }

    public function bindEvent()
    {
        $this->events = array_replace_recursive($this->events, config('event')) ?: [];
    }

    public function runEvent(string $event, array $args = [])
    {
        if ($this->arrayTools -> offsetExists($event)) {
            $event = $this->app -> make($this->arrayTools -> offsetGet($event), isNew: true);
            return $this->app -> invokeMethod([$event, 'handle'], $args);
        } else {
            throw new \Error("event error: ${event} not exists");
        }
    }

}