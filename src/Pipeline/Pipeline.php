<?php

namespace iflow\Pipeline;

class Pipeline {

    protected array $pipes = [];


    public function registerPipeline(mixed $pipeline): Pipeline {
        $this -> pipes[] = $pipeline;
        return $this;
    }

    public function through($pipes): Pipeline {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }


    public function process(mixed $args, ?callable $destination = null): void {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            fn($passable) => $destination === null ? true : $destination($passable)
        );
        $pipeline($args);
    }

    protected function carry(): \Closure {
        return fn ($stack, $pipe) => fn ($passable) => $pipe($passable, $stack);
    }
}