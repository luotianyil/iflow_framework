<?php

namespace iflow\annotation\Db\Column;

use iflow\annotation\Db\Model;
use iflow\annotation\Db\Table;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Index extends Column {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function __construct(public string|array $indexType = ColumnIndexType::INDEX) {
    }

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        return $reflector;
    }


    public function handle(Reflector $reflector, Table $table, Model $model, array &$args): mixed {
        $this -> property = $reflector;
        $this -> setTableColumnIndexes($table, $this -> getColumName());
        return true;
    }

    protected function getTableColumStructure(): array {
        return [];
    }
}