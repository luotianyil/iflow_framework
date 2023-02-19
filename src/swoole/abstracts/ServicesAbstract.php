<?php

namespace iflow\swoole\abstracts;

use iflow\Container\Container;
use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\fileSystem\Watch;
use iflow\initializer\appMonitoring;
use iflow\swoole\Config;
use iflow\swoole\implement\Tools\Pid;
use iflow\swoole\implement\Tools\Task\Delivery;
use iflow\swoole\implement\Tools\Task\Finish;
use iflow\swoole\interfaces\ServicesInterface;
use iflow\swoole\ServicesCommand;
use Simps\MQTT\Client as MQClient;
use Swoole\Coroutine\Client as SwooleClient;
use Swoole\Http\Server as HttpServer;
use Swoole\Process;
use Swoole\Server;
use Swoole\WebSocket\Server as WebSocketServer;
use function Co\run;

abstract class ServicesAbstract implements ServicesInterface {

    protected HttpServer|WebSocketServer|Server|SwooleClient|MQClient $SwService;

    protected Pid $pid;

    protected Config $config;

    protected array $_params = [];

    protected array $events = [];

    protected array $serverListeners;

    #[Inject]
    public Delivery $delivery;

    #[Inject]
    public Finish $finish;

    protected string $defaultEventClass = '';

    public function __construct(protected ServicesCommand $servicesCommand) {
        $this->config = $this->servicesCommand -> getConfig();
    }

    public function start() {
        // TODO: Implement start() method.

        $this->events['task'] = [ $this->delivery, 'onTask' ];
        $this->events['finish'] = [ $this->finish, 'onFinish' ];
        $this->events['WorkerStart'] = [ $this, 'onWorkerStart' ];

        $this -> setPid($this->config -> get('swConfig@pid_file'))
              -> setServerParams();

        $serviceClass = $this->getSwooleServiceClass();

        $this->SwService = new $serviceClass(...$this->_params);
        $this->SwService -> set($this->config -> get('swConfig'));
        $this->servicesCommand -> setServices();

        $this->registerListeners() -> createServiceAfter();

        $process = new Process(function () {
            run(function () {
                Container::getInstance() -> make(Watch::class) -> initializer($this->servicesCommand -> app);
                Container::getInstance() -> make(appMonitoring::class) -> initializer($this->servicesCommand -> app);
            });
        });
        $this->getSwService() -> addProcess($process);
    }

    protected function createServiceAfter() {}

    /**
     * @return HttpServer|Server|SwooleClient|WebSocketServer|MQClient
     */
    public function getSwService(): HttpServer|Server|SwooleClient|WebSocketServer|MQClient {
        return $this->SwService;
    }

    protected function setPid(string $pid): ServicesAbstract {
        $this->pid = new Pid($pid);
        return $this;
    }

    /**
     * 结束服务
     * @return bool
     */
    public function stop(): bool {
        // TODO: Implement stop() method.
        if ($this->pid -> isRun()) {
            $this->pid -> kill(SIGTERM);
            $this->servicesCommand->Console -> writeConsole ->writeLine('> swoole server stop success');
        } else {
            $this->servicesCommand->Console -> writeConsole ->writeLine('no swoole server process running. ');
        }
        return true;
    }

    /**
     * 重启服务
     * @return bool
     */
    public function reload(): bool {
        if ($this->pid -> isRun()) {
            $this->servicesCommand -> Console -> writeConsole ->writeLine('no swoole server process running.');
            return false;
        }

        $this->servicesCommand->Console -> writeConsole ->writeLine('Stopping swoole server...');
        $isRunning = $this->pid->kill(SIGTERM);

        if ($isRunning) {
            $this->servicesCommand->Console -> writeConsole ->writeLine('Unable to stop the swoole_server process.');
            return false;
        }
        $this->start();
        return true;
    }

    /**
     * 获取服务类
     * @return string
     */
    protected function getSwooleServiceClass(): string {
        return Server::class;
    }

    protected function setServerParams(): ServicesAbstract {

        $this->_params = $this->config -> get('port') ? [
            $this->config -> get('host'),
            $this->config -> get('port')
        ] : [];

        if (empty($this->_params))
            $this->_params = $this->config -> get('host') ?: $this->config -> get('listener');
        $this->_params = array_values($this->_params);
        return $this;
    }

    /**
     * 事件注册
     * @param object $eventObject
     * @return $this
     */
    protected function registerSwServiceEvent(object $eventObject): ServicesAbstract {
        foreach ($this->events as $eventName => $event) {
            $eventCallback = is_array($event) ? [ $event[0], $event[1] ] : [ $eventObject, $event ];
            $server = is_array($event) && count($event) > 2 ? $event[2] : $this->getSwService();

            $server -> on($eventName, $eventCallback);
        }
        return $this;
    }

    /**
     * 输出初始化基础信息
     * @param string|array $protocol_name
     * @return void
     */
    protected function printStartContextToConsole(string|array $protocol_name = 'http'): void {
        $runMemoryUsage = round(memory_get_usage() / 1024 / 1024, 2);

        $protocol_name = is_string($protocol_name) ? [ $protocol_name ] : $protocol_name;

        $info = 'SERVER_ADDRESS: ';

        $host = $this->servicesCommand -> config -> get('host') ?: $this->servicesCommand -> config -> get('listener');

        $host = is_array($host) ? $host : [
            'host' => $this->servicesCommand -> config -> get('host'),
            'port' => $this->servicesCommand -> config -> get('port')
        ];

        foreach ($protocol_name as $name) {
            $info .= $name . '://' . $host['host'] . ':' . $host['port'] . PHP_EOL;
        }

        $info .= "runMemoryUsage: " . $runMemoryUsage . "M";
        $servicesType = sprintf("%s %s", implode('/', $protocol_name), $this->servicesCommand -> isStartServer() ? 'Server' : 'Client');
        $this->servicesCommand -> Console -> writeConsole ->writeLine($info.PHP_EOL.'> Start '. $servicesType .' Success');
    }

    /**
     * 增加监听的端口
     * @return $this
     */
    protected function registerListeners(): ServicesAbstract {
        $listeners = $this->config -> get('listeners');
        foreach ($listeners as $listenerName => $listener) {

            $listener['options'] = $listener['options'] ?? 'default';

            $listenerServer = $this->SwService -> addlistener($listener['host'], $listener['port'], $listener['mode'] ?? SWOOLE_SOCK_TCP);

            $listenerServer -> set(
                $listener['options'] === 'default' ? $this->config -> get('swConfig') : $listener['options']
            );

            $this->serverListeners[$listenerName] = $listenerServer;
        }

        return $this;
    }

    public function onTask() {}

    public function onFinish() {}

    public function onWorkerStart() {}

    protected function getEventClass(): string {
        return $this->servicesCommand -> config -> get('event') ?: $this->defaultEventClass;
    }

    /**
     * @return ServicesCommand
     */
    public function getServicesCommand(): ServicesCommand {
        return $this->servicesCommand;
    }


    /**
     * 追加监听事件
     * @param array $events
     * @param object $event
     * @param object|null $server 指定监听服务
     * @return ServicesAbstract
     */
    public function addEventValues(array $events, object $event, ?object $server = null): ServicesAbstract {
        foreach ($events as $eventName => $eventMethod) {
            $this->events[$eventName] = $server ? [ $event, $eventMethod,  $server] : [ $event, $eventMethod ];
        }
        return $this;
    }

    /**
     * 通过名称获取监听服务
     * @param string $name
     * @return mixed
     */
    public function getListenerServer(string $name): mixed {
        return $this->serverListeners[$name] ?? null;
    }
}
