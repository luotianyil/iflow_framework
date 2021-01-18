<?php


namespace iflow\Swoole;


use iflow\console\lib\Command;
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

    public function handle()
    {
        $this->userEvent = explode('-', $this->Console -> input -> getUserCommand()[1]);

        if ($this->userEvent[1] !== 'service') {
            $configKeys = $this->userEvent[1];
            $configKeys = $configKeys . '@' . (empty($this->userEvent[2]) ? 'server' : $this->userEvent[2]);
        } else {
            $configKeys = 'service';
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

    public function eventInit($class = '', array $event = []) {
        $class = is_object($class) ? $class : $this;
        $event = $event ?: $this->event;

        foreach ($event as $key => $value) {
            $this-> server -> on($key, function () use ($class, $value) {
                call_user_func([$class, $value], ...func_get_args());
            });
        }
    }

    public function onStart($serve)
    {
        $server = 'SERVER_ADDRESS: '.$serve -> host.':'.$serve -> port;
        $this->Console -> outPut ->writeLine($server.PHP_EOL.'> Start Success');
    }

    public function onTask()
    {}

    // 启动
    protected function start()
    {
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