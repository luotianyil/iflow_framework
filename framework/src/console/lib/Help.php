<?php


namespace iflow\console\lib;


use iflow\fileSystem\File;
use iflow\fileSystem\lib\fileSystem;
use iflow\fileSystem\lib\local\local;

class Help extends Command
{
    public function handle()
    {
//        $content = "";
//        foreach ($this->Console -> command as $key => $value) {
//            $key = is_numeric($key) ? $value : $key;
//            $content .= "\033[20;1H\033[1;4;32m{$key}\033[0m".PHP_EOL;
//        }
//        $this->Console -> outWrite($content);
        $this->test();
    }

    public function test()
    {
        // files('G:\360MoveData\Users\Master\Desktop\84777669_p1.jpg') -> md5()
        var_dump(files('framework\src\fileSystem\File.php') -> getSize());
    }
}