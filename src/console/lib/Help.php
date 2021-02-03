<?php


namespace iflow\console\lib;


use iflow\fileSystem\File;
use iflow\fileSystem\lib\fileSystem;
use iflow\fileSystem\lib\local\local;

class Help extends Command
{
    public function handle()
    {
        $content = "";
        foreach ($this->Console -> command as $key => $value) {
            $key = is_numeric($key) ? $value : $key;
            $content .= $key.PHP_EOL;
        }
        $this->Console -> outWrite($content);
    }
}