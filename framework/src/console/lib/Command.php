<?php


namespace iflow\console\lib;


use iflow\App;
use iflow\console\Console;

class Command
{
    public App $app;
    public Console $Console;

    /**
     * @param App $app
     */
    public function setApp(App $app): void
    {
        $this->app = $app;
    }

    /**
     * @param Console $Console
     */
    public function setConsole(Console $Console): void
    {
        $this->Console = $Console;
    }
}