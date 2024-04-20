<?php

namespace iflow\http\Hook\Interfaces;

use iflow\App;
use iflow\Request;
use iflow\Response;

interface RequestHookInterface {

    public function handle(App $app, Request $request, Response $response, ...$args): mixed;

}
