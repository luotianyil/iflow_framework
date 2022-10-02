<?php

namespace iflow\event;

use Closure;
use Exception;
use iflow\App;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\event\lib\Abstracts\SubjectAbstract;
use iflow\event\lib\AppDefaultEvent\RequestEndEvent;
use iflow\Helper\Arr\Arr;
use iflow\http\Kernel\Request\RequestInitializer;
use RuntimeException;

class Event {

    protected App $app;
    protected Arr $arrayTools;

    /**
     * 时间列表
     * @var array|string[]
     */
    protected array $events = [
        'RequestEndEvent' => RequestEndEvent::class,
        'RequestVerification' => RequestInitializer::class
    ];

    /**
     * @throws InvokeClassException
     */
    public function initializer(App $app) {
        $this -> app = $app;
        $this->events = array_merge($this->events, config('event') ?: []);

        foreach ($this->events as $name => $event) {
            $event = $this->app -> make($event);
            if (!$event instanceof SubjectAbstract) {
                throw new RuntimeException($event::class . ' instanceof SubjectAbstract fail');
            }
            $this->bind($name, $event);
        }
        $this->arrayTools = new Arr($this->events);
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
        return $this->app -> invoke([ $event, 'trigger' ], $args);
    }

    /**
     * 获取已注册事件列表
     * @param string $eventName
     * @return SubjectAbstract|null
     */
    public function getEvent(string $eventName): ?SubjectAbstract {
        return $this->events[$eventName] ?? null;
    }
}