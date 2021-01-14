<?php


namespace iflow\facade;


use iflow\Facede;

/**
 * Class Log
 * @mixin \iflow\log\Log
 * @package iflow\facade
 */
class Log extends Facede
{
    protected static function getFacedeClass(): string
    {
        return 'iflow\log\Log';
    }
}