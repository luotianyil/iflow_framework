<?php

namespace iflow\annotation\Db\Interfaces;

use iflow\annotation\Db\Model;
use iflow\annotation\Db\Table;

interface DBInterface {

    public function handle(\Reflector $reflector, Table $table, Model $model, array &$args): mixed;

}
