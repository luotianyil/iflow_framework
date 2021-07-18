<?php


namespace iflow\Swoole;


use Swoole\Table;

class Tables
{
    protected array $tables = [];

    public function add(string $name, Table $table): static
    {
        $this->tables[$name] = $table;
        return $this;
    }

    public function get(string $name)
    {
        return $this->tables[$name] ?? null;
    }

    public function getAll(): array
    {
        return $this->tables;
    }

    public function __get(string $name)
    {
        // TODO: Implement __get() method.
        return $this->get($name);
    }

}