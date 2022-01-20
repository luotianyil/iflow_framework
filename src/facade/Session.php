<?php


namespace iflow\facade;


use iflow\Faceted;

/**
 * Class Session
 * @mixin \iflow\session\Session
 * @package iflow\facade
 */
class Session extends Faceted {
    protected static function getFaceClass(): string
    {
        return \iflow\session\Session::class;
    }
}