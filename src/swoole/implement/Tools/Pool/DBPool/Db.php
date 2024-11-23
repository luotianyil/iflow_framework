<?php

namespace iflow\swoole\implement\Tools\Pool\DBPool;

use iflow\Helper\Arr\Arr;
use iflow\swoole\implement\Tools\Pool\DBPool\Connection\DbConnection;
use think\db\ConnectionInterface;
use think\DbManager;

class Db extends DbManager {

    protected function createConnection(string $name): ConnectionInterface {
        $config = new Arr($this->config);

        if (!$config -> get('pool@db.enable')) {
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