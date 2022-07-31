<?php


namespace iflow\socket\workman\http;


use Workerman\Worker;

class httpServer
{
    protected Worker $server;

    protected string $type = 'http';

    public function __construct(protected array $config = []) {
        $this->type = $this->config['type'] ?? 'http';
    }

    protected function event(): bool {
        $classes = $this->config['handle'];
        if (!class_exists($classes)){
            $this->stop();
            return false;
        }
        $onEvent = new $classes($this->config, $this->server);
        foreach ($onEvent -> events as $event => $action) {
            $this->server->{$event} = [$onEvent, $action];
        }
        return true;
    }

    public function start(): string {

        if (empty($this->config['host']) || empty($this->config['port'])) {
            return 'WorkerMan Config is empty !!!';
        }

        $address = "{$this -> type}://{$this -> config['host']}:{$this -> config['port']}";
        $this->server = new Worker(
            $address, $this->config['context'] ?? []
        );

        $this->server -> name = uniqid();
        if (!$this->event()) {
            return "Start {$this -> type} Services Fail CallBack Event Classes Not Exists";
        }
        Worker::runAll();
        return "start Server Success \r\n Server For WorkManServer \r\n serverAddress: {$address}";
    }

    public function stop() {
        Worker::stopAll();
    }

    public function reload() {
        Worker::reloadAllWorkers();
    }
}