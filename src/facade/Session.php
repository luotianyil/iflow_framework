<?php


namespace iflow\facade;


use iflow\Facede;

/**
 * Class Session
 * @mixin \iflow\session\Session
 * @package iflow\facade
 */
class Session extends Facede
{
    protected static function getFacedeClass(): string
    {
        return \iflow\session\Session::class;
    }
}