<?php

namespace iflow\event;

use Closure;
use Exception;
use iflow\App;
use iflow\event\lib\Abstracts\SubjectAbstract;
use iflow\Utils\ArrayTools;

class Event {

    protected App $app;
    protected ArrayTools $arrayTools;

    protected array $events = [];

    public function initializer(App $app) {
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
     * @param SubjectAbstract|Closure $event
     * @return Event
     */
    public function bind(string $name, SubjectAbstract|Closure $event): static {
        $this->events[$name] = $event;
        return $this;
    }

    /**
     * 触发事件
     * @param string $event
     * @param array $args
     * @throws Exception
     * @return mixed
     */
    public function trigger(string $event, array $args = []): mixed {
        $event = $this->events[$event] ?? null;
        if (!$event) throw new Exception("event error: $event not exists");
        if ($event instanceof Closure) {
            return $this->app -> invoke($event, $args);
        }
        return $this->app -> invoke([$event, 'trigger'], $args);
    }

}