<?php


namespace iflow\Swoole;


use iflow\console\lib\Command;
use iflow\fileSystem\Watch;
use Swoole\Process;

class Services extends Command
{
    use Server;

    protected array $event = [
        'start' => 'onStart',
        'task' => 'onTask'
    ];

    protected array $initializers = [];

    public array $config = [];
    public array $userEvent = [];
    public float $runMemoryUsage = 0.00;

    public function handle(array $event = [])
    {
        $this->userEvent = $event;
        if ($this->userEvent[1] !== 'service') {
            $configKeys = $this->userEvent[1];
            $configKeys = 'swoole.'. $configKeys . '@' . (empty($this->userEvent[2]) ? 'server' : $this->userEvent[2]);
        } else {
            $configKeys = 'swoole.service';
        }

        $this->config = config($configKeys);
        $this->initializers($this);
        match($this->userEvent[0]) {
            'stop' => $this->stop(),
            'reload' => $this->reStart(),
            default => ''
        };
        if ($this->userEvent[0] !== 'start') exit(1);
        $this->run();
    }

    public function run()
    {}

    public function eventInit($class = '', array $event = [], $server = null) {
        $class = is_object($class) ? $class : $this;
        $event = $event ?: $this->event;
        $server = $server ?: $this->server;
        foreach ($event as $key => $value) {
            $server -> on($key, function () use ($class, $value) {
                call_user_func([$class, $value], ...func_get_args());
            });
        }
    }

    public function onStart($serve)
    {}

    public function onTask()
    {}

    // 启动
    protected function start()
    {
        $this->runMemoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
        $info = 'SERVER_ADDRESS: '.$this->server -> host.':'.$this->server -> port. PHP_EOL;
        $info .= "runMemoryUsage: " . $this->runMemoryUsage . "M";
        $this->Console -> outPut ->writeLine($info.PHP_EOL.'> Start Success');
        $process = new Process(function () {
            \Co\run(function () {
                (new Watch()) -> initializer($this->app);
            });
        });
        $this->getServer() -> addProcess($process);
        $this->getServer() -> start();
    }

    // 重启
    protected function reStart(): bool
    {
        if (!$this->pid->isRun()) {
            $this->Console -> outPut ->writeLine('no swoole server process running.');
            return false;
        }

        $this->Console -> outPut ->writeLine('Stopping swoole server...');
        $isRunning = $this->pid->kill(SIGTERM);

        if ($isRunning) {
            $this->Console -> outPut ->writeLine('Unable to stop the swoole_server process.');
            return false;
        }
        $this->initializer();
        $this->Console -> outPut ->writeLine('> success');
        return true;
    }

    // 停止
    protected function stop(): bool
    {
        if ($this->pid -> isRun()) {
            $this->pid -> kill(SIGTERM);
            $this->Console -> outPut ->writeLine('> swoole server stop success');
        } else {
            $this->Console -> outPut ->writeLine('no swoole server process running. ');
        }
        return true;
    }

    public function initializer(): bool
    {
        if ($this->pid -> isRun()) {
            $this->Console -> outPut ->writeLine('swoole server process running.');
        } else {
            $this->Console -> outPut ->writeLine('start swoole server.');
            foreach ($this->initializers as $key) {
                $this->app->make($key) -> initializer($this);
            }
            $this->eventInit();
            $this->start();
        }
        return true;
    }

    public function callConfigHandle($pack = '', $param = [])
    {
        if (class_exists($this->services -> Handle)) {
            return call_user_func([new $this->services -> Handle, 'Handle'], ...$param);
        }
        return [];
    }
}