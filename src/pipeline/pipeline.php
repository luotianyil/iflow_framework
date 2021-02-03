<?php


namespace iflow\pipeline;


use iflow\App;
use iflow\Response;

class pipeline
{
    protected array $queue = [];

    public function through($class)
    {
        $this->queue[] = $class;
    }

    public function process(App $app): array|object
    {
        $call = [];
        foreach ($this->queue as $key => $value) {
            $class = is_numeric($key) ? $value : $key;
            $method = is_numeric($key) ? 'handle' : $value;
            $callBack = call_user_func([$app->make($class), $method], ...func_get_args());

            unset($this->queue[$key]);
            if ($callBack instanceof Response) return $callBack;
            $call[] = $callBack;
        }
        return $call;
    }
}