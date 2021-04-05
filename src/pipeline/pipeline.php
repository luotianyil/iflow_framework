<?php


namespace iflow\pipeline;


use iflow\App;
use iflow\Response;

class pipeline
{
    protected array $pipes = [];

    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }


    public function process($app, $destination = null)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            function ($passable) use ($destination) {
                return $destination === null ? true : $destination($passable);
            }
        );
        $pipeline($app);
    }

    protected function carry(): \Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                return $pipe($passable, $stack);
            };
        };
    }
}