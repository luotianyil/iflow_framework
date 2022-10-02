<?php


namespace iflow\initializer;


use iflow\App;
use iflow\Helper\Tools\System;

class appSurroundings {

    protected App $app;

    protected array $config = [
        'ext' => [
            'zlib',
            'bcmath',
            'mbstring',
            'json',
            'fileinfo'
        ]
    ];

    public function initializer(App $app) {
        $this->app = $app;
        $this->config = array_replace_recursive($this->config, config('app@appSurroundings')) ?? $this->config;
        $this->validateExt();
    }

    protected function validateExt(): static {

        $extension = System::extensionLoaded($this->config['ext']);
        if (count($extension) > 0) {
            throw new \Exception("extension: $extension[0] not installed");
        }
        return $this;
    }
}