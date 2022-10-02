<?php

namespace iflow\template\Adapter\Regx;

use iflow\template\config\Config;
use iflow\template\template;

class RenderView extends template {

    public function display(string $content = '', array $vars = [], array|Config $config = []): string {
        if ($vars) {
            $this->data = array_merge($this->data, $vars);
        }

        if ($config) {
            $this->config($config);
        }

        $viewRenderCode = '';

        return $this->render(
            $this->saveCacheFile($content, $viewRenderCode)
        );
    }

    public function fetch(string $template = '', array $vars = [], array|Config $config = []): string {
        if ($vars) {
            $this->data = array_merge($this->data, $vars);
        }

        if ($config) {
            $this->config($config);
        }

        $file = $this->config -> getViewRootPath() . $template . '.' . $this->config -> getViewSuffix();
        $this->exists($file);

        $content = file_get_contents($file);
        $viewRenderCode = '';
        //

        return $this->render(
            $this->saveCacheFile($template, $viewRenderCode)
        );
    }

}
