<?php

namespace iflow\swoole\implement\Tools\Pool\DBPool\Connection;

use iflow\swoole\implement\Tools\Pool\DBPool\ProxyPool;
use Psr\SimpleCache\CacheInterface;
use think\db\BaseQuery;
use think\db\ConnectionInterface;
use think\DbManager;

class DbConnection extends ProxyPool implements ConnectionInterface {


    public function getQueryClass(): string
    {
        // TODO: Implement getQueryClass() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function table($table)
    {
        // TODO: Implement table() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function name($name)
    {
        // TODO: Implement name() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function connect(array $config = [], $linkNum = 0)
    {
        // TODO: Implement connect() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function setDb(DbManager $db)
    {
        // TODO: Implement setDb() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function setCache(CacheInterface $cache)
    {
        // TODO: Implement setCache() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getConfig(string $config = '')
    {
        // TODO: Implement getConfig() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function close()
    {
        // TODO: Implement close() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function find(BaseQuery $query): array
    {
        // TODO: Implement find() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function select(BaseQuery $query): array
    {
        // TODO: Implement select() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function insert(BaseQuery $query, bool $getLastInsID = false)
    {
        // TODO: Implement insert() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function insertAll(BaseQuery $query, array $dataSet = []): int
    {
        // TODO: Implement insertAll() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function update(BaseQuery $query): int
    {
        // TODO: Implement update() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function delete(BaseQuery $query): int
    {
        // TODO: Implement delete() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function value(BaseQuery $query, string $field, $default = null)
    {
        // TODO: Implement value() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function column(BaseQuery $query, array|string $column, string $key = ''): array
    {
        // TODO: Implement column() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function transaction(callable $callback)
    {
        // TODO: Implement transaction() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function startTrans()
    {
        // TODO: Implement startTrans() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function commit()
    {
        // TODO: Implement commit() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getTableFields(string $tableName): array
    {
        // TODO: Implement getTableFields() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getLastSql(): string
    {
        // TODO: Implement getLastSql() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getLastInsID(BaseQuery $query, string $sequence = null)
    {
        // TODO: Implement getLastInsID() method.
        return $this->__call(__FUNCTION__, func_get_args());
    }
}