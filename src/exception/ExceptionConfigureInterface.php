<?php

namespace iflow\exception;

use iflow\App;

interface ExceptionConfigureInterface {

    public function configure(\Throwable $throwable, App $app, \Closure $next, array $args = []): mixed;

}