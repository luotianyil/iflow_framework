<?php

namespace iflow\console\Adapter;

use iflow\App;
use iflow\console\Adapter\Argument\Argument;
use iflow\console\Console;

abstract class Command {

    use Argument;

    public App $app;

    public Console $Console;

    public Console $console;

    /**
     * @param App $app
     * @return Command
     */
    public function setApp(App $app): static {
        $this->app = $app;
        return $this;
    }

    /**
     * @param Console $console
     * @return Command
     */
    public function setConsole(Console $console): static {
        $this->Console = $console;
        $this->console = $console;
        return $this;
    }

    /**
     * @return $this
     */
    public function setArgument(): static {
        $this->parserArgumentInstruction($this -> Console -> input);
        return $this;
    }

    abstract public function handle(array $event = []);

}