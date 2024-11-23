<?php

namespace iflow\annotation\Db\Column;

use iflow\annotation\Db\Interfaces\DBInterface;
use iflow\annotation\Db\Model;
use iflow\annotation\Db\Table;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Helper\Str\Str;
use Reflector;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Column extends AnnotationAbstract implements DBInterface {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public string|array $indexType = '';

    public string $referencesTable = '';

    public string $references = '';

    protected \Reflector $property;

    public function __construct(
        public string $name = '',
        public string $type = '',
        public int $length = 255,
        public string $description = '',
        public string $defaultValue = '',
        public bool $nullable = true,
        public bool $primaryKey     = false,
        public bool $auto_increment = false,
    ) {}

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        return $reflector;
    }

    public function handle(\Reflector $reflector, Table $table, Model $model, array &$args): mixed {
        // TODO: Implement handle() method.
        $this -> property = $reflector;
        $columName = $this -> getColumName();

        $table -> withTableStructure("columns.$columName", $this -> getTableColumStructure());
        $this -> setTableColumnIndexes($table, $columName);
        return true;
    }


    /**
     * 获取字段名称
     * @return string
     */
    protected function getColumName(): string {
        if (empty($this->name)) $this -> name = Str::humpToLower($this->property->getName());
        return $this -> name;
    }

    /**
     * 获取字段结构
     * @return array
     */
    protected function getTableColumStructure(): array {
        return [
            'name'          => $this -> getColumName(),
            'type'          => $this -> type,
            'length'        => $this -> getLength(),
            'description'   => $this -> description,
            'defaultValue'  => $this -> defaultValue,
            'autoIncrement'  => $this -> auto_increment,
            'nullable'      => $this -> nullable,
            'primaryKey'    => $this -> primaryKey,
            'indexType'     => $this -> indexType,
        ];
    }

    protected function getLength(): int {
        return $this->length;
    }

    protected function getTableColumnIndex(string $indexType, string $colum = ''): array {
        $colum = $colum ?: $this -> getColumName();

        return [
            'column_name'      => $colum,
            'index_name'       => $colum . '_' . strtolower($indexType) . '_idx',
            'index_type'       => strtoupper($indexType),
            'references_table' => $this -> referencesTable,
            'references'       => $this -> references,
        ];
    }


    protected function setTableColumnIndexes(Table &$table, string $colum): void {
        $indexType = is_string($this->indexType) ? [ $this->indexType ] : $this -> indexType;

        foreach ($indexType as $indexTypeValue) {
            $index   = $this  -> getTableColumnIndex($indexTypeValue, $colum);
            if (!$index['index_type']) continue;
            $table -> withTableStructure('indexes.'.$colum.'.'.$index['index_type'], $index);
        }
    }
}