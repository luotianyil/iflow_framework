<?php


namespace iflow\facade;


use iflow\Facede;

/**
 * @mixin \iflow\cache\Cache
 * Class Cache
 * @package iflow\facade
 */
class Cache extends Facede
{

    protected static function getFacedeClass(): string
    {
        return "iflow\cache\Cache";
    }

}