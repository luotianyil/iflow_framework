<?php

namespace iflow\annotation\Db\Adapter\Trait;

use iflow\annotation\Db\Interfaces\CreateTableAdapterInterface;

trait DiffTableTrait  {


    protected function diff(CreateTableAdapterInterface $adapter, array $table_structure, array $last_table_structure): bool {
        foreach ($table_structure['columns'] as $columnName => $column) {
            $lastColumn = $last_table_structure['columns'][$columnName] ?? [];
            if (empty($lastColumn)) {
                $adapter -> addColumn($adapter -> getTable(), $column);
                continue;
            }

            if (array_diff_assoc($lastColumn, $column)) {
                $adapter -> modifyColumn($adapter -> getTable(), $column);
            }
        }

        $this -> checkColumnIndex($adapter, $table_structure['indexes'], $last_table_structure['indexes'])
              -> checkAwaitRemoveColumns($adapter, $table_structure['columns'], $last_table_structure['columns'] ?? [])
              -> checkAwaitRemoveIndexes($adapter, $table_structure['indexes'], $last_table_structure['indexes']);

        return true;
    }

    protected function checkColumnIndex(CreateTableAdapterInterface $adapter, array $index, array $lastIndex): CreateTableAdapterInterface {
        foreach ($index as $column => $indexList) {
            foreach ($indexList as $indexType => $index) {
                if (empty($lastIndex[$column][$indexType] ?? []))
                    $adapter -> addIndex($adapter -> getTable(), $index);
            }
        }
        return $this;
    }

    /**
     * 移除废弃字段
     * @param CreateTableAdapterInterface $adapter
     * @param array $columns
     * @param array $lastColumns
     * @return bool
     */
    protected function checkAwaitRemoveColumns(CreateTableAdapterInterface $adapter, array $columns, array $lastColumns): CreateTableAdapterInterface {
        $nowColumnNames = array_keys($columns);
        $lastColumnNames = array_keys($lastColumns);

        // 移除字段
        foreach ($lastColumnNames as $lastColumnName) {
            if (!in_array($lastColumnName, $nowColumnNames)) {
                $adapter -> removeColumn($adapter -> getTable(), $lastColumns[$lastColumnName]);
            }
        }
        return $this;
    }


    /**
     * 移除废弃索引
     * @param CreateTableAdapterInterface $adapter
     * @param array $indexes
     * @param array $lastIndexes
     * @return bool
     */
    protected function checkAwaitRemoveIndexes(CreateTableAdapterInterface $adapter, array $indexes, array $lastIndexes): mixed {

        foreach ($lastIndexes as $indexColumn => $index) {
            if (empty($indexes[$indexColumn] ?? [])) return array_map(fn ($idx) => $adapter -> removeIndex(
                $adapter -> getTable(), $idx
            ), $index);

            foreach ($index as $indexType => $idx) {
                if (empty($indexes[$indexColumn][$indexType] ?? [])) $adapter -> removeIndex(
                    $adapter -> getTable(), $idx
                );
            }
        }
        return true;
    }


}