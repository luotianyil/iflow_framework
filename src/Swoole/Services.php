<?php


namespace iflow\Swoole;


use iflow\console\lib\Command;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\fileSystem\Watch;
use iflow\initializer\appMonitoring;
use Swoole\Process;
use function Co\run;

class Services extends Command
{
    use Server;

    protected array $event = [
        'start' => 'onStart',
        'task' => 'onTask',
        'finish' => 'onFinish'
    ];

    protected array $initializers = [];

    public array $config = [];
    public array $userEvent = [];
    public float $runMemoryUsage = 0.00;

    public function handle(array $event = []): bool {
        $this->userEvent = $event;
        if ($this->userEvent[1] !== 'service') {
            $configKeys = $this->userEvent[1];
            $configKeys = 'swoole.'. $configKeys . '@' . (empty($this->userEvent[2]) ? 'server' : $this->userEvent[2]);
        } else {
            $configKeys = 'swoole.service';
        }

        $this->config = config($configKeys);

        if (empty($this->config['swConfig'])) {
            $this->Console -> outPut -> writeLine('Swoole Config is Empty !!!');
            return true;
        }

        $this->initializers($this);
        match($this->userEvent[0]) {
            'stop' => $this->stop(),
            'reload' => $this->reStart(),
            default => ''
        };

        if ($this->userEvent[0] !== 'start') return true;
        $this->app -> register(Services::class, $this);
        $this->run();
        return true;
    }

    public function run() {}

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

    // 服务启动回调
    public function onStart($serve) {}

    // 异步投递回调
    public function onTask($serv, $task_id, $reactor_id, $data) {}

    // 异步投递执行完毕回调
    public function onFinish($serv, $task_id, $data) {}

    // 启动
    protected function start()
    {
        $this->runMemoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
        $info = 'SERVER_ADDRESS: '.$this->server -> host.':'.$this->server -> port. PHP_EOL;
        $info .= "runMemoryUsage: " . $this->runMemoryUsage . "M";
        $servicesType = sprintf("%s %s", $this->userEvent[1] ?? 'http', $this->userEvent[2] ?? '');
        $this->Console -> outPut ->writeLine($info.PHP_EOL.'> Start '. $servicesType .' Success');
        $process = new Process(function () {
            run(function () {
                (new Watch()) -> initializer($this->app);
                (new appMonitoring) -> initializer($this->app);
            });
        });
        $this->getServer() -> addProcess($process);
        $this->getServer() -> start();
    }

    /**
     * 重启服务
     * @return bool
     * @throws InvokeClassException
     */
    protected function reStart(): bool {
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

    /**
     * 停止服务
     * @return bool
     */
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

    /**
     * 初始化服务
     * @return bool
     * @throws InvokeClassException
     */
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

    /**
     * 执行回调
     * @param string $object
     * @param array $param
     * @return mixed
     * @throws InvokeClassException
     */
    public function callConfigHandle(string $object = '', array $param = []): mixed {
        $object = $object !== '' ? $object : $this->services -> Handle;
        if (class_exists($object)) {
            $object = $this->app -> make($object);
            if (method_exists($object, 'Handle'))
                return call_user_func([new $object, 'Handle'], ...$param);
        }
        return [];
    }

    /**
     * 启动服务类型
     * @return bool
     */
    protected function isStartServer(): bool {
        if (empty($this->userEvent[2])) {
            $this->userEvent[2] = $this->userEvent[1] === 'service' ? 'server' : 'client';
        }
        return $this->userEvent[2] !== 'client';
    }
}