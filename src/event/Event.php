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

    /**
     * 绑定事件
     * @param array $events
     */
    public function bindEvent(array $events = [])
    {
        $this->events = array_merge($events, $this->events);
    }

    /**
     * 触发事件
     * @param string $event
     * @param array $args
     * @return mixed
     */
    public function trigger(string $event, array $args = []): mixed
    {
        $eventName = explode('.', $event);
        $method = "";

        if (count($eventName) > 1) [$eventName, $method] = $eventName;
        else $eventName = $eventName[0];

        if ($this->arrayTools -> offsetExists($eventName)) {
            $class = $this->app -> make($this->arrayTools -> offsetGet($eventName), isNew: true);
            return $this->app -> invokeMethod([$class, $method ?: 'handle'], $args);
        }
        throw new \Error("event error: ${eventName} not exists");
    }

}