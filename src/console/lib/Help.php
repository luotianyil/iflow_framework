<?php


namespace iflow\console\lib;


use iflow\fileSystem\File;
use iflow\fileSystem\lib\fileSystem;
use iflow\fileSystem\lib\local\local;

class Help extends Command
{
    public function handle(array $event = [])
    {
        $content = "";
        if (empty($event[0]) || ($event[0] !== 'help' || count($event) > 1))
            $content .= "Unknown instruction: ". implode(' ', $this->Console -> input -> getUserCommand()) ."\r\n\r\n";
        foreach ($this->Console -> command as $key => $value) {
            $key = is_numeric($key) ? $value : $key;
            $content .= $key.PHP_EOL;
        }
        $this->Console -> outWrite($content);
        return true;
    }
}