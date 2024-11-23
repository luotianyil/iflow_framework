<?php

namespace iflow\annotation\Db;

use Reflector;
use iflow\annotation\Db\Traits\{
    TableTrait,
    GenerateTableSqlTrait,
    TableAutoMargeTrait
};
use iflow\Container\implement\annotation\tools\data\abstracts\DataAbstract;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Table extends DataAbstract {

    use TableTrait, GenerateTableSqlTrait, TableAutoMargeTrait;

    protected Model $model;

    public function __construct(
        protected string $table = '',
        protected string $classTableField = 'table',
        protected array $options = []
    ) {}

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        $this -> model = $this -> getObject($args);

        $this -> withTableNameTyProperty($reflector, $this -> classTableField, $this->table);
        $this -> withTableOptions($this->options);

        $this -> generate($reflector, $args);

        $this -> setTable($this->model, $reflector, $this -> classTableField, $this->table);

        return $reflector;
    }

}
