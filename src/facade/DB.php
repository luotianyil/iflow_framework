<?php

namespace iflow\facade;

use iflow\Faceted;

use iflow\swoole\implement\Tools\Pool\DBPool\Db as DbPool;

/**
 * @mixin DbPool
 */
class DB extends Faceted {

    protected static function getFaceClass(): string {
        // TODO: Implement getFaceClass() method.
        return DbPool::class;
    }
}