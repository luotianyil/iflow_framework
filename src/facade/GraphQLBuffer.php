<?php


namespace iflow\facade;


use iflow\Faceted;
use iflow\Swoole\GraphQL\Buffer\Buffer;

class GraphQLBuffer extends Faceted
{

    protected static function getFaceClass(): string
    {
        // TODO: Implement getFaceClass() method.
        return Buffer::class;
    }
}