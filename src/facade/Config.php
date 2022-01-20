<?php


namespace iflow\facade;


use iflow\Faceted;

/**
 * @see \iflow\initializer\Config
 * @mixin \iflow\initializer\Config
 * Class Config
 * @package iflow\facade
 */
class Config extends Faceted {
    protected static function getFaceClass(): string {
        return \iflow\initializer\Config::class;
    }
}