<?php


namespace iflow\fileSystem;


use iflow\App;
use iflow\console\Console;
use iflow\event\Event;
use iflow\fileSystem\implement\fileSystem;
use iflow\Utils\Tools\Timer;
use Swoole\Server;

class Watch {

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

    public function initializer(App $app): bool {
        if (!config('app@hot_update', false)) return false;
        go(function () use ($app) {
            $this->app = $app;
            $this->file = app(File::class) -> initializer();
            app(Event::class) -> bindEvent($this->config['watchChangeEvent']);
            $this->WatchFile() -> startWatch();
        });
        return true;
    }


    protected function WatchFile(): Watch {
        $this->files = [];
        foreach ($this->config['watchFolder'] as $dir) {
            find_files($this->app -> getRootPath() . DIRECTORY_SEPARATOR . $dir, function (\SplFileInfo $splFileInfo) {
                $this->files[$splFileInfo -> getPathname()] = $splFileInfo -> getMTime();
            });
        }
        return $this;
    }

    public function startWatch(): void {
        Timer::tick(1000, function () {
            foreach ($this->files as $key => $value) {
                if ($value < (new fileSystem($key)) -> getMTime()) {
                    $this -> reload();
                    break;
                }
            }
        });
    }

    private function excludeEvent(): void {
        foreach ($this->config['watchChangeEvent'] as $key => $value) {
            event($key, $this);
        }
    }

    private function reload(): Watch {
        $this->app -> make(Server::class) -> stop();

        // 重新运行应用
        $this->app -> runApp();
        $this->app -> make(Console::class) -> outPut -> writeLine('reload success ...');
        return $this;
    }
}