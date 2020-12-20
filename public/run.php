<?php

require "../vendor/autoload.php";

#[\iflow\router\lib\RouterAnnotation]
class MyApplication {
    public function run($namespace = '')
    {

    }
}

(new MyApplication()) -> run();

