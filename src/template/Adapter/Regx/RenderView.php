<?php

namespace iflow\template\Adapter\Regx;

use iflow\Container\Container;
use iflow\template\Adapter\Regx\RegxInterpreter\RegxInterpreter;
use iflow\template\config\Config;
use iflow\template\Adapter\Regx\Config as RConfig;
use iflow\template\template;

class RenderView extends template {

    public function __construct(protected array|Config $config) {
        if (is_array($this->config)) {
            $this->config = new RConfig($this->config);
        }
    }

    public function display(string $content = '', array $vars = [], array|Config $config = []): string {
        if ($vars) {
            $this->data = array_merge($this->data, $vars);
        }

        if ($config) $this->config($config);

        $regx = Container::getInstance() -> make(
            RegxInterpreter::class,
            [ $content, $this->config ]
        );

        return $this->render(
            $this->saveCacheFile($content, $regx -> getPhpTemplateCode())
        );
    }

    public function fetch(string $template = '', array $vars = [], array|Config $config = []): string {
        if ($vars) {
            $this->data = array_merge($this->data, $vars);
        }

        if ($config) $this->config($config);

        $file = $this->config -> getViewRootPath() . $template . '.' . $this->config -> getViewSuffix();
        $this->exists($file);

        $content = file_get_contents($file);
        $regx = Container::getInstance() -> make(
            RegxInterpreter::class,
            [ $content, $this->config ]
        );

        return $this->render(
            $this->saveCacheFile($template, $regx -> getPhpTemplateCode())
        );
    }

}
