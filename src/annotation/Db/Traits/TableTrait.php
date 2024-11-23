<?php

namespace iflow\annotation\Db\Traits;

use iflow\annotation\Db\Table;

trait TableTrait {


    protected function setTable(object $model, \Reflector $reflector, string $propertyName, string $table): void {
        $property = $reflector->getProperty($propertyName);
        $property -> setValue($model, $this->withTableNameTyProperty($reflector, $propertyName, $table));
    }


    protected function withTableOptions(array $options) : Table {
        $options = array_merge($this -> getTableStructure() -> get('table'), $options);
        $this -> withTableStructure('table', $options);

        return $this;
    }

    protected function withTableNameTyProperty(\Reflector $reflector, string $propertyName, string $table): string {
        if ($tableName = $this -> getTableStructure() -> get('table@name')) {
            return $tableName;
        }

        $property = $reflector -> getProperty($propertyName);
        $tableName = $table ?: $property -> getDefaultValue();
        $this -> setTableName($tableName);
        return $tableName;
    }

}