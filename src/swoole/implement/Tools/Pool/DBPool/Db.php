<?php

namespace iflow\swoole\implement\Tools\Pool\DBPool;

use iflow\Helper\Arr\Arr;
use iflow\swoole\implement\Tools\Pool\DBPool\Connection\DbConnection;
use Swoole\Coroutine;
use think\db\ConnectionInterface;
use think\DbManager;

class Db extends DbManager {

    /**
     * 创建数据库连接
     * @param string $name
     * @return ConnectionInterface
     */
    protected function createConnection(string $name): ConnectionInterface {
        $config = new Arr($this->config);

        // 未启动连接池/或者未运行至协程内时 不启用连接池连接
        if (!$config -> get('pool@db.enable') || (swoole_success() && Coroutine::getCid() === -1)) {
            return parent::createConnection($name);
        }

        return new DbConnection(new class(fn() => parent::createConnection($name)) extends DbConnector {
            public function disconnect($connection): void {
                if ($connection instanceof ConnectionInterface) {
                    $connection->close();
                }
            }
        }, $config -> get('pool@db', []));
    }

}