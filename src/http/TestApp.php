<?php

namespace iflow\http;

use Exception;
use iflow\App;
use iflow\event\Event;
use iflow\initializer\AppSurroundings;
use iflow\initializer\Config;
use iflow\initializer\Error;
use iflow\initializer\Helpers;
use iflow\initializer\initializer;
use iflow\log\Log;

class TestApp extends App {

    protected array $initializers = [
        Config::class,
        Helpers::class,
        Event::class,
        Log::class,
        Error::class,
        AppSurroundings::class,
        initializer::class
    ];

    /**
     * @throws Exception
     */
    public function runApp() {
        // TODO: Implement runApp() method.
        $this -> run();
    }
}