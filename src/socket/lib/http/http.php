<?php
declare (strict_types = 1);

namespace iflow\socket\lib\http;


use iflow\socket\lib\interfaces\services;
use iflow\Utils\Tools\Timer;

class http implements services
{

    protected mixed $socketServer = null;
    protected array $event = [];

    protected request $request;
    protected response $response;

    public function __construct(
        public string $host = "127.0.0.1",
        public int $port = 8080,
        protected array $options = []
    ) {
        $this->options['packSize'] = $this->options['packSize'] ?? 9024;
    }

    public function start()
    {
        // TODO: Implement start() method.
        $this->triggerEvent('beforestart', $this); // 启动前回调
        $this->createSocketServer() -> wait();
    }

    /**
     * 创建 socket Http 服务器
     * @return $this
     * @throws \Exception
     */
    public function createSocketServer(): static
    {
        // TODO: Implement createSocketServer() method.
        if ($this->socketServer === null) {
            $this->socketServer = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_block($this->socketServer);
            // 绑定端口
            socket_bind($this->socketServer, $this->host, $this->port);
        }
        $this->triggerEvent('afterstart', $this); // 启动后回调
        return $this;
    }

    /**
     * 监听 socket 数据
     * @return $this
     */
    public function wait(): static
    {
        // TODO: Implement wait() method.
        socket_listen($this->socketServer, 4);
        Timer::tick(0, function () {
            $sock = socket_accept($this->socketServer);
            // 验证请求数据
            if ($sock) {
                $pack = socket_read($sock, 2048);
                if ($pack) {
                    try {
                        $this->request = new request($pack, $sock, $this->options);
                        $this->response = new response($sock);
                        // 接收到 请求后的回调
                        $this->triggerEvent('request', $this->request, $this->response);
                    } catch (\Exception $exception) {
                        $this->close($sock);
                    }
                }
            }
        });
        return $this;
    }

    /**
     * 关闭 socket 连接
     * @param null $socket
     */
    public function close($socket = null)
    {
        // TODO: Implement close() method.
        $socket = $socket ?: $this->socketServer;
        socket_shutdown($socket);
        socket_close($socket);
    }

    /**
     * 事件绑定
     * @param string $event
     * @param callable $func
     */
    public function on(string $event, callable $func)
    {
        $this->event[strtolower($event)] = $func;
    }

    /**
     * 触发回调事件
     * @param string $event
     * @param mixed ...$args
     */
    protected function triggerEvent(string $event, ...$args)
    {
        if (!empty($this->event[$event])) {
            // throw new \Exception("callBack event: $event not exists");
            call_user_func($this->event[$event], ...$args);
        }
    }
}