<?php

namespace iflow\swoole\Sandbox\ReSetter;

use iflow\App;
use iflow\swoole\Sandbox\SandboxApplication;

class ReSetterSandBoxProperty {

    protected array $property = [];

    public function ReSetterProperty(SandboxApplication $sandboxApplication, App $app): App {
        $app -> instance('config', clone $sandboxApplication -> getBaseApplication() -> config);
        return $app;
    }

}