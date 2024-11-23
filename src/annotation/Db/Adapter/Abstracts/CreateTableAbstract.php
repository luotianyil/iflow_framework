<?php

namespace iflow\annotation\Db\Adapter\Abstracts;

use iflow\annotation\Db\Adapter\Trait\DiffTableTrait;
use iflow\annotation\Db\Interfaces\CreateTableAdapterInterface;
use iflow\facade\DB;
use think\db\ConnectionInterface;

abstract class CreateTableAbstract implements CreateTableAdapterInterface {

    use DiffTableTrait;

    protected string $sql;

    protected bool $exists = false;

    /**
     * 数据连接配置
     * @var string
     */
    protected string $connection;

    /**
     * 待构建表
     * @var string
     */
    protected string $table;

    /**
     * 数据库连接句柄
     * @var ConnectionInterface
     */
    protected ConnectionInterface $connectHandle;

    abstract protected function getCreateTableSql(array $table_structure): string;

    abstract protected function getTypeLength(string $type, int $length): string;


    public function handle(array $options): bool {
        // TODO: Implement handle() method.
        $this->connection = $options['connection'];
        $this->table      = $options['table_structure']['table']['name'];

        $this->connectHandle = DB::connect($this->connection);

        $this->exists = $this -> checkTableExists($this->table, $options['connection']);

        if ($this->exists) {
            return $this -> diff(
                $this,
                $options['table_structure'],
                $options['last_table_structure']
            );
        }

        $this->connectHandle -> execute(
            $this -> getCreateTableSql($options['table_structure'])
        );

        foreach ($options['table_structure']['indexes'] as $columnIndexMap) {
            array_map(fn ($index) => $this -> addIndex($this->table, $index), $columnIndexMap);
        }

        return true;
    }

    public function columnSql(array $column): string {
        return sprintf(
            '%s %s %s %s %s COMMENT %s',
            $column['name'],
            $this -> getTypeLength($column['type'], $column['length']),
            $this -> getPrimaryColumnType($column),
            !$column['nullable'] ? ' NOT NULL' : '',
            $column['defaultValue'] ? ' DEFAULT \'' . $column['defaultValue'] . '\'' : ($column['nullable'] ? 'NULL' : ''),
            '\'' . $column['description'] . '\''
        );
    }
    public function getTable(): string {
        // TODO: Implement getTable() method.
        return $this->table;
    }

    public function toSql(): string {
        // TODO: Implement toSql() method.
        return $this->sql;
    }

}