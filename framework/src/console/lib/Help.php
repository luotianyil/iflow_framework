<?php


namespace iflow\console\lib;


use iflow\console\Console;

class Help extends Command
{
    public function handle()
    {
        $content = "";
        foreach ($this->Console -> command as $key => $value) {
            $key = is_numeric($key) ? $value : $key;
            $content .= "\033[20;1H\033[1;4;32m{$key}\033[0m".PHP_EOL;
        }

        $this->Console -> outWrite($content);
    }
}