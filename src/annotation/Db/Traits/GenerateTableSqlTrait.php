<?php

namespace iflow\annotation\Db\Traits;

use iflow\annotation\Db\Interfaces\DBInterface;
use iflow\annotation\Db\Table;
use iflow\Helper\Arr\Arr;

trait GenerateTableSqlTrait {

    protected Arr $tableStructure;

    /**
     * 获取表结构
     * @return Arr
     */
    public function getTableStructure(): Arr {
        return $this->tableStructure ??= new Arr([
            'table' => [
                'name'      => '',
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'engine'    => 'InnoDB',
                'comment'   => ''
            ],
            'columns' => [],
            'indexes' => []
        ]);
    }


    /**
     * 构建数据库表
     * @param \Reflector $reflector
     * @param array $args
     * @return void
     * @throws \ReflectionException
     */
    public function generate(\Reflector $reflector, array &$args): void {
        $attributes     = $reflector -> getAttributes();
        $properties     = $reflector -> getProperties();

        $this -> execute($reflector, $attributes, $args)
              -> generateColumns($properties, $args)
              -> autoMarge()
              -> setLastTableCache();
    }


    /**
     * @param \ReflectionProperty[] $properties
     * @param array $args
     * @return Table
     * @throws \ReflectionException
     */
    protected function generateColumns(array $properties, array &$args): Table {
        foreach ($properties as $property) {
            $this -> execute($property, $property -> getAttributes(), $args);
        }

        return $this;
    }

    /**
     * 设置表名
     * @param string $tableName
     * @return Table
     */
    public function setTableName(string $tableName): Table {
        $this->getTableStructure() -> offsetSet('table.name', $tableName);
        return $this;
    }

    /**
     * 设置表结构
     * @param string $name
     * @param mixed $value
     * @return Table
     */
    public function withTableStructure(string $name, mixed $value): Table {
        $this->getTableStructure() -> offsetSet($name, $value);
        return $this;
    }

    /**
     * 执行注解
     * @param \Reflector $reflector
     * @param \Reflector[] $attributes
     * @param array $args
     * @return Table
     * @throws \ReflectionException
     */
    protected function execute(\Reflector $reflector, array $attributes, array &$args): Table {
        $object = $this -> getObject($args);

        foreach ($attributes as $attribute) {
            $attributeRefClass = new \ReflectionClass($attribute -> getName());
            if (!in_array(DBInterface::class, $attributeRefClass -> getInterfaceNames())) continue;

            $attributeRefClass -> newInstance(...$attribute -> getArguments()) -> handle($reflector, $this, $object, $args);
        }

        return $this;
    }
}
