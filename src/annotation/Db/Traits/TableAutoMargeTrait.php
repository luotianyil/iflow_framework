<?php

namespace iflow\annotation\Db\Traits;

use iflow\annotation\Db\Adapter\DBAdapter;
use iflow\annotation\Db\Table;
use iflow\Container\implement\annotation\exceptions\AttributeTypeException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use iflow\facade\DB;

trait TableAutoMargeTrait {

    protected function setLastTableCache(): Table {
        $path = $this -> getTableCachePath();
        if (!$path) return $this;

        !is_dir(dirname($path)) && mkdir(dirname($path), 0755, true);
        file_put_contents($path, serialize($this -> getTableStructure() -> toArray()));
        return $this;
    }

    protected function getLastTableCache(): array {
        $path = $this -> getTableCachePath();
        if (!$path || !file_exists($path)) return [];

        return unserialize(file_get_contents($path));
    }


    protected function getTableCachePath(): string {
        $path = config('database@auto_marge.table_cache');
        if (!$path) return '';

        return $path . DIRECTORY_SEPARATOR . $this -> getTableStructure() -> get('table@name');
    }

    /**
     * 创建表
     * @return Table
     * @throws AttributeTypeException|InvokeClassException|InvokeFunctionException
     */
    protected function autoMarge(): Table {
        try {
            if (!config('database@auto_marge.enable')) return $this;

            DB::startTrans();

            $dbType = $this -> model -> getConfig()['type'];

            (new DBAdapter()) -> handle(
                $dbType === 'sqlsrv' ? 'msSql' : $dbType,
                'createTable',
                [
                    'table_structure'      => $this -> getTableStructure() -> all(),
                    'last_table_structure' => $this -> getLastTableCache() ?: [
                        'columns' => [],
                        'indexes' => []
                    ],
                    'connection'           => $this -> model -> getConnection()
                ]
            );

            DB::commit();
            return $this;
        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }
}
