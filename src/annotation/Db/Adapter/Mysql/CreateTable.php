<?php

namespace iflow\annotation\Db\Adapter\Mysql;

use iflow\annotation\Db\Adapter\Abstracts\CreateTableAbstract;
use iflow\annotation\Db\Column\ColumnIndexType;
use iflow\annotation\Db\Column\ColumnType;

class CreateTable extends CreateTableAbstract {

    protected function getCreateTableSql(array $table_structure): string {
        // TODO: Implement getCreateTableSql() method.
        return sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` (\n%s\n) ENGINE=%s COMMENT='%s' CHARACTER SET = %s COLLATE = %s;",
            $this->table,
            implode(',' . PHP_EOL, array_map(
                fn ($column) => $this -> columnSql($column), $table_structure['columns']
            )),
            $table_structure['table']['engine'],
            $table_structure['table']['comment'],
            $table_structure['table']['charset'],
            $table_structure['table']['collation'],
        );
    }

    public function getPrimaryColumnType(array $column): string {
        if (!$column['primaryKey']) return '';
        return sprintf(
            'PRIMARY KEY %s',
            $column['autoIncrement'] ? ' AUTO_INCREMENT' : ''
        );
    }

    protected function getTypeLength(string $type, int $length): string {
        if ($length === 255 && strtoupper($type) !== ColumnType::STRING) return $type;
        return "$type($length)";
    }

    public function checkTableExists(string $table, string $connection): bool {
        // TODO: Implement checkTableExists() method.
        $sql = "SHOW TABLES LIKE '$table'";
        return count($this->connectHandle -> query($sql)) > 0;
    }

    public function removeColumn(string $table, array $column): bool {
        // TODO: Implement removeColumn() method.
        return $this->connectHandle -> execute(
            "ALTER TABLE `$table` DROP COLUMN `{$column['name']}`"
        );
    }

    public function addColumn(string $table, array $column): bool {
        // TODO: Implement addColumn() method.
        if (count($this->connectHandle -> query("SHOW COLUMNS FROM $table LIKE '{$column['name']}'")) > 0)
            return true;

        return $this->connectHandle -> execute(
            sprintf("ALTER TABLE `%s` ADD COLUMN %s", $table, $this -> columnSql($column))
        );
    }

    public function modifyColumn(string $table, array $column): bool {
        // TODO: Implement modifyColumn() method.
        return $this->connectHandle -> execute(
            sprintf("ALTER TABLE `%s` MODIFY %s", $table, $this -> columnSql($column))
        );
    }

    public function removeIndex(string $table, array $index): bool {
        // TODO: Implement removeIndex() method.
        return $this->connectHandle -> execute(
            "ALTER TABLE $table DROP INDEX {$index['index_name']}"
        );
    }

    public function addIndex(string $table, array $index): bool {
        // TODO: Implement addIndex() method.
        if ($index['index_type'] === ColumnIndexType::PRIMARY) return true;

        return $this->connectHandle -> execute(
            sprintf(
                "ALTER TABLE %s ADD %s `%s`(`%s`) %s",
                $table,
                $index['index_type'],
                $index['index_name'],
                $index['column_name'],
                $index['index_type'] === ColumnIndexType::FOREIGN
                    ? "REFERENCES {$index['references_table']}(`{$index['references']}`)" : ''
            )
        );
    }

}