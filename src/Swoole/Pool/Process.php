<?php


namespace iflow\Swoole\Pool;


use iflow\Swoole\Pool\lib\pool;

class Process extends pool
{

    private array $events = [
        'WorkerStart',
        'WorkerStop'
    ];

    public function setName()
    {}

    public function startProcess(string|\Closure $WorkerStart, string|\Closure $WorkerStop)
    {
        $argc = func_get_args();
        foreach ($this->events as $key => $event) {
            $this->on($event, function ($pool, $workerId) use ($key, $event, $argc){
                if (is_string($argc[$key])) {
                    $ref = new \ReflectionClass($argc[$key]);
                    call_user_func([$ref -> newInstance(), 'handle'], ...[$pool, $workerId, $key]);
                } else {
                    call_user_func($argc[$key], ...[$pool, $workerId, $key]);
                }
            });
        }
        $this->start();
    }

    public function stop()
    {

    }

    /**
     * 批量创建协程
     * @param array $coroutine
     * @return array
     */
    public function create(array $coroutine): array {
        $coroutineId = [];
        foreach ($coroutine as $name => $value) {
            $coroutineId[] = \Swoole\Coroutine::create($value['fn'], ...$value['params']);
        }
        return $coroutineId;
    }
}