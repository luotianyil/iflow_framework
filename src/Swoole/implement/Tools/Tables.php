<?php

namespace iflow\swoole\implement\Tools;

use Swoole\Table;

class Tables {

    protected array $tables = [];

    public function add(string $name, Table $table): static {
        $this->tables[$name] = $table;
        return $this;
    }

    public function get(string $name, bool $createTable = false) {
        $table = $this->tables[$name] ?? null;

        if ($table === null && $createTable) {
            $table = new Table(2048);
            $this->add($name, $table);
        }

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