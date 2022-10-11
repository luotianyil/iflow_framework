<?php


namespace iflow\http;

use iflow\event\Event;
use iflow\http\Adapter\Request;
use iflow\http\Adapter\Response;
use iflow\initializer\{ appSurroundings, Config, Error, Helpers, initializer };
use iflow\log\Log;

abstract class App extends \iflow\App {

    protected array $initializers = [
        Config::class,
        Helpers::class,
        Event::class,
        Log::class,
        Error::class,
        appSurroundings::class,
        initializer::class
    ];

    public function initializer(): App {
        parent::initializer(); // TODO: Change the autogenerated stub
        event('RequestVerification', new Request(), new Response(), microtime(true));
        return $this;
    }
}