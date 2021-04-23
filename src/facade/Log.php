<?php


namespace iflow\facade;


use iflow\Faceted;

/**
 * Class Log
 * @mixin \iflow\log\Log
 * @package iflow\facade
 */
class Log extends Faceted
{
    protected static function getFaceClass(): string
    {
        return 'iflow\log\Log';
    }
}