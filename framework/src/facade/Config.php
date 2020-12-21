<?php


namespace iflow\facade;


use iflow\Facede;

/**
 * @see \iflow\initializer\Config
 * @mixin \iflow\initializer\Config
 * Class Config
 * @package iflow\facade
 */
class Config extends Facede
{

    protected static function getFacedeClass(): string
    {
        return 'iflow\initializer\Config';
    }

}