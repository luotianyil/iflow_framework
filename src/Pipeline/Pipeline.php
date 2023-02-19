<?php

namespace iflow\Pipeline;

class Pipeline {

    protected array $pipes = [];

    public function through($pipes): static {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }


    public function process($app, ?callable $destination = null): void {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            fn($passable) => $destination === null ? true : $destination($passable)
        );
        $pipeline($app);
    }

    protected function carry(): \Closure {
        return fn ($stack, $pipe) => fn ($passable) => $pipe($passable, $stack);
    }
}