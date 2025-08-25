<?php

namespace iflow\swoole\implement\Tools\Pool\DBPool\TDEngine;

use think\db\BaseQuery;
use think\db\Connection;

class TDEngineConnection extends Connection {

    public function getQueryClass(): string
    {
        // TODO: Implement getQueryClass() method.
    }

    public function connect(array $config = [], $linkNum = 0) {
        // TODO: Implement connect() method.
        if (isset($this->links[$linkNum])) {
            return $this->links[$linkNum];
        }

        // $config =
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function find(BaseQuery $query): array
    {
        // TODO: Implement find() method.
    }

    public function select(BaseQuery $query): array
    {
        // TODO: Implement select() method.
    }

    public function insert(BaseQuery $query, bool $getLastInsID = false)
    {
        // TODO: Implement insert() method.
    }

    public function insertAll(BaseQuery $query, array $dataSet = []): int
    {
        // TODO: Implement insertAll() method.
    }

    public function update(BaseQuery $query): int
    {
        // TODO: Implement update() method.
    }

    public function delete(BaseQuery $query): int
    {
        // TODO: Implement delete() method.
    }

    public function value(BaseQuery $query, string $field, $default = null)
    {
        // TODO: Implement value() method.
    }

    public function column(BaseQuery $query, array|string $column, string $key = ''): array
    {
        // TODO: Implement column() method.
    }

    public function transaction(callable $callback)
    {
        // TODO: Implement transaction() method.
    }

    public function startTrans()
    {
        // TODO: Implement startTrans() method.
    }

    public function commit()
    {
        // TODO: Implement commit() method.
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }

    public function getTableFields(string $tableName): array
    {
        // TODO: Implement getTableFields() method.
    }

    public function getLastSql(): string
    {
        // TODO: Implement getLastSql() method.
    }

    public function getLastInsID(BaseQuery $query, string $sequence = null)
    {
        // TODO: Implement getLastInsID() method.
    }
}