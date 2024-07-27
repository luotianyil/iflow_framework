<?php

namespace iflow\swoole\implement\Tools\Task;

use iflow\Container\Container;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use Swoole\Server;

class Delivery {

    protected array $data = [];

    /**
     * @throws InvokeFunctionException
     * @throws InvokeClassException
     */
    public function onTask(Server $server, int $task_id, int $reactor_id, mixed $data): mixed {
        $data = is_array($data) ? $data : (json_decode($data, true) ?: unserialize($data));

        if (!is_array($data)) return false;

        $this->data = $data;
        $callable = $this->getTaskCallable();
        if (!$callable[0]) return false;

        $class = Container::getInstance() -> make($callable[0], isNew: true);

        // 回调函数不存在返回 NULL
        if (!method_exists($class, $callable[1])) {
            return $server -> finish(null);
        }

        return $server -> finish(
            Container::getInstance() -> invoke(
                [ $class, $callable[1] ], [ ...$this->getTaskCallableParams(), $task_id, $reactor_id ]
            )
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
            $param[] = $value['type'] === 'object' ? Container::getInstance() -> make(
                $value['value'], $value['args'] ?? [], $value['isNew'] ?? false
            ) : $value;
        }
        return $param;
    }

}