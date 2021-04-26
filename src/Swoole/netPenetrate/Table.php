<?php


namespace iflow\Swoole\netPenetrate;


class Table extends \Swoole\Table
{
    public function getAll(): array
    {
        $table = [];
        foreach ($this as $value) {
            $table[] = $value;
        }
        return $table;
    }
}