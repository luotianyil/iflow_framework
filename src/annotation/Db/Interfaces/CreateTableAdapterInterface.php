<?php

namespace iflow\annotation\Db\Interfaces;

interface CreateTableAdapterInterface extends AdapterInterface {


    public function columnSql(array $column): string;

    public function getPrimaryColumnType(array $column): string;

    /**
     * 检查表是否存在
     * @param string $table
     * @param string $connection
     * @return bool
     */
    public function checkTableExists(string $table, string $connection): bool;

    /**
     * 删除字段
     * @param string $table
     * @param array $column
     * @return bool
     */
    public function removeColumn(string $table, array $column): bool;

    /**
     * 新增字段
     * @param string $table
     * @param array $column
     * @return mixed
     */
    public function addColumn(string $table, array $column): bool;

    /**
     * 修改表字段
     * @param string $table
     * @param array $column
     * @return bool
     */
    public function modifyColumn(string $table, array $column): bool;

    /**
     * 删除索引
     * @param string $table
     * @param array $index
     * @return bool
     */
    public function removeIndex(string $table, array $index): bool;

    /**
     * 新增索引
     * @param string $table
     * @param array $index
     * @return bool
     */
    public function addIndex(string $table, array $index): bool;

    public function getTable(): string;

    /**
     * @return string
     */
    public function toSql(): string;

}