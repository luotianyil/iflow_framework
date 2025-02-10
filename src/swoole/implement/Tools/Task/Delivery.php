<?php

namespace iflow\swoole\implement\Tools\Task;

use iflow\Container\Container;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use Swoole\Server;

class Delivery {

    protected array $data = [];

    protected int|Server\Task $task;

    protected Server $server;

    /**
     * @throws InvokeFunctionException
     * @throws InvokeClassException
     */
    public function onTask(Server $server, int|Server\Task $task, int $reactor_id = 0, mixed $data = ''): mixed {
        $this->task   = $task;
        $this->server = $server;

        $task_id = $task;

        if ($task instanceof Server\Task) {
            $task_id = $task->id;
            $data = $task -> data;
            $reactor_id = $task -> worker_id;
        }

        $data = is_array($data) ? $data : (json_decode($data, true) ?: unserialize($data));

        if (!is_array($data)) return false;

        $this->data = $data;
        $callable = $this->getTaskCallable();
        if (!$callable[0]) return false;

        $class = Container::getInstance() -> invokeClass($callable[0]);

        // 回调函数不存在返回 NULL
        if (!method_exists($class, $callable[1])) return $this -> finish(null);

        if (!$server -> setting['task_enable_coroutine']) {
            return $this->finish($this->run([ $class, $callable[1] ], $task_id, $reactor_id));
        }

        go(fn () => $this -> finish(
            $this->run([ $class, $callable[1] ], $task_id, $reactor_id)
        ));

        return true;
    }


    protected function run($callback, $task_id, $reactor_id): mixed {
        return Container::getInstance() -> invoke(
            $callback, [ ...$this->getTaskCallableParams(), $task_id, $reactor_id ]
        );
    }

    protected function getTaskCallable(): string|array {
        return $this->data['callable'] ?: [];
    }

    protected function getTaskCallableParams(): array {

        $params = $this->data['callable_params'];
        $param = [];

        foreach ($params as $value) {
            if (!is_array($value) || empty($value)) {
                $param[] = $value;
                continue;
            }

            if ($value['type'] === 'object') {
                $value = ($value['isNew'] ?? false)
                    ? app() -> invokeClass($value['value'], $value['args'] ?? [])
                    : app($value['value'], $value['args'] ?? []);
            }

            $param[] = $value;
        }

        return $param;
    }

    protected function finish(mixed $data): bool {
        if ($this->task instanceof Server\Task) return $this->task -> finish($data);
        return $this->server -> finish($data);
    }

}