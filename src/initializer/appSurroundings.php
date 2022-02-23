<?php


namespace iflow\initializer;


use iflow\App;
use iflow\Utils\Tools\SystemTools;

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
        $extension = (new SystemTools()) -> get_extension_loaded($this->config['ext']);
        if (count($extension) > 0) {
            throw new \Exception("extension: $extension[0] not installed");
        }
        return $this;
    }
}