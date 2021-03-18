<?php


namespace iflow\fileSystem;


use iflow\App;
use iflow\console\Console;
use iflow\event\Event;
use iflow\fileSystem\lib\fileSystem;
use iflow\Utils\Tools\Timer;

class Watch
{

    private App $app;

    protected File $file;

    private array $config = [
        'watchFolder' => [
            'app',
            'config'
        ],
        'watchChangeEvent' => []
    ];

    private array $files = [];

    public function initializer(App $app)
    {
        if (!config('app@hot_update')) return false;
        go(function () use ($app) {
            $this->app = $app;
            $this->file = app(File::class) -> initializer();
            app(Event::class) -> bindEvent($this->config['watchChangeEvent']);
            $this->WatchFile() -> startWatch();
        });
        return true;
    }


    private function WatchFile(): static
    {
        $this->files = [];
        foreach ($this->config['watchFolder'] as $key => $value) {
            array_multi_to_one(
                $this->file -> fileList -> loadFileList(
                    $this->app -> getRootPath() . DIRECTORY_SEPARATOR . $value, traverse: true
                ), $this->files, function ($file) {
                    if (file_exists($file)) {
                        $this->files[$file] = (new fileSystem($file)) -> getMTime();
                    }
                    return false;
                }
            );
        }
        return $this;
    }

    public function startWatch()
    {
        Timer::tick(1000, function () {
            foreach ($this->files as $key => $value) {
                if ($value < (new fileSystem($key)) -> getMTime()) {
                    $this -> reload() -> WatchFile() -> excludeEvent();
                    break;
                }
            }
        });
    }

    private function excludeEvent() {
        foreach ($this->config['watchChangeEvent'] as $key => $value) {
            event($key, $this);
        }
    }

    private function reload(): static {
        $this->app -> make(\Swoole\Server::class) -> reload();
        $this->app -> make(Console::class) -> outPut -> writeLine('reload success ...');
        return $this;
    }
}