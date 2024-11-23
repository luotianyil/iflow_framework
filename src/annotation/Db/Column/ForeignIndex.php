<?php

namespace iflow\annotation\Db\Column;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ForeignIndex extends Index {

    public string|array $indexType = ColumnIndexType::FOREIGN;

    public function __construct(public string $referencesTable, public string $references) {
    }

}