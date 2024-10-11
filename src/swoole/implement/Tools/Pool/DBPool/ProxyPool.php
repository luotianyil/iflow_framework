<?php

namespace iflow\swoole\implement\Tools\Pool\DBPool;

use iflow\swoole\implement\Tools\Pool\Coroutine\Context;
use Smf\ConnectionPool\ConnectionPool;
use Smf\ConnectionPool\Connectors\ConnectorInterface;
use Swoole\Coroutine;

abstract class ProxyPool {

    protected ConnectionPool $pool;

    protected \WeakMap $connectionPool;

    protected \WeakMap $disconnectionPool;

    public function __construct(ConnectorInterface $connector, array $config, array $connectionConfig = []) {

        $this->connectionPool = new \WeakMap();
        $this->disconnectionPool = new \WeakMap();

        $this->pool = new ConnectionPool($config, $connector, $connectionConfig);

        if (method_exists($connector, 'setCheckerConnection')) {
            $connector->setCheckerConnection(function ($connection) {
                return !isset($this->disconnectionPool[$connection]);
            });
        }

        $this->pool -> init();
    }


    protected function getConnectionPool() {
        return Context::rememberData('connectionPoolProxy' . spl_object_id($this), function () {
            $connection = $this -> pool -> borrow();
            $this -> connectionPool[$connection] = false;

            Coroutine::defer(function () use ($connection) {
                $this -> setConnection($connection);
            });
            return $connection;
        });
    }


    public function setConnection($connection): bool {
        if ($this->connectionPool[$connection] ?? false) return false;

        $this->connectionPool[$connection] = true;
        return $this->pool -> return($connection);
    }

    public function __call(string $name, array $arguments): mixed {
        // TODO: Implement __call() method.
        $connection = $this->getConnectionPool();
        if ($this->released[$connection] ?? false) {
            throw new \RuntimeException('Connection already has been released!');
        }

        try {
            return $connection->{$name}(...$arguments);
        } catch (\Exception|\Throwable $e) {
            $this->disconnectionPool[$connection] = true;
            throw $e;
        }
    }

}