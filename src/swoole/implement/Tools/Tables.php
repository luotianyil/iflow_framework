<?php

namespace iflow\swoole\implement\Tools;

use Swoole\Table;

class Tables {

    protected array $tables = [];

    public function createTable(string $name, array $config = []): Table {
        $table = new Table($config['size'] ?? 1024);
        foreach (($config['fields'] ?? []) as $field) {
            $table -> column($field['name'], $field['type'], $field['size'] ?? 1024);
        }
        $table->create();
        $this->add($name, $table);
        return $table;
    }

    public function add(string $name, Table $table): Tables {
        $this->tables[$name] = $table;
        return $this;
    }

    public function get(string $name, bool $createTable = false): ?Table {
        $table = $this->tables[$name] ?? null;
        if ($createTable) $table = $this->createTable($name);
        return $table;
    }

    public function getAll(): array {
        return $this->tables;
    }

    public function __get(string $name) {
        // TODO: Implement __get() method.
        return $this->get($name);
    }

}