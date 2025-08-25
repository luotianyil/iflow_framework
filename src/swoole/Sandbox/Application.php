<?php

namespace iflow\swoole\Sandbox;


use iflow\App;
use iflow\Container\Container;
use iflow\swoole\Sandbox\ReSetter\ReSetterSandBoxProperty;

class Application {

    protected SandboxApplication $sandbox;

    protected App $sandboxApplication;

    protected ReSetterSandBoxProperty $reSetterSandBoxProperty;

    public function runApp(SandboxApplication $app): App {
        $this -> sandbox = $app;
        $this -> sandboxApplication = clone $this -> sandbox -> getBaseApplication();
        return $this -> ReSetterSandBoxProperty();
    }

    protected function ReSetterSandBoxProperty(): App {
        $this -> reSetterSandBoxProperty = new ReSetterSandBoxProperty();
        Container::setInstance(fn () => $this -> sandboxApplication);

        return $this -> reSetterSandBoxProperty -> ReSetterProperty(
            $this -> sandbox, $this -> sandboxApplication);
    }

}
