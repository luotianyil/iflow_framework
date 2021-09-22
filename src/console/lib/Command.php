<?php


namespace iflow\console\lib;


use iflow\App;
use iflow\console\Console;

abstract class Command
{

    use Argument;

    public App $app;
    public Console $Console;

    /**
     * @param App $app
     * @return Command
     */
    public function setApp(App $app): static
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @param Console $Console
     * @return Command
     */
    public function setConsole(Console $Console): static
    {
        $this->Console = $Console;
        return $this;
    }

    /**
     * @return $this
     */
    public function setArgument(): static
    {
        $this->parserArgumentInstruction($this -> Console -> input);
        return $this;
    }

    abstract public function handle(array $event = []);
}