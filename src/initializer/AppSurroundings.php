<?php


namespace iflow\initializer;


use iflow\App;
use iflow\Helper\Tools\System;

class AppSurroundings {

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

    /**
     * @param App $app
     * @return void
     * @throws \Exception
     */
    public function initializer(App $app): void {
        $this->app = $app;
        $this->config = array_replace_recursive($this->config, config('app@appSurroundings')) ?? $this->config;
        $this->validateExt();
    }

    /**
     * @throws \Exception
     */
    protected function validateExt(): AppSurroundings {
        $extension = System::extensionLoaded($this->config['ext']);
        if (count($extension) > 0) {
            throw new \Exception("extension: $extension[0] not installed");
        }
        return $this;
    }
}