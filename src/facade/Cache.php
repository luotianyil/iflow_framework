<?php


namespace iflow\facade;


use iflow\Faceted;

/**
 * @mixin \iflow\cache\Cache
 * Class Cache
 * @package iflow\facade
 */
class Cache extends Faceted
{

    protected static function getFaceClass(): string
    {
        return "iflow\cache\Cache";
    }

}