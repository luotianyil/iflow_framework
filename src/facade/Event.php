<?php


namespace iflow\facade;


use iflow\Faceted;

class Event extends Faceted
{

    protected static function getFaceClass(): string
    {
        // TODO: Implement getFaceClass() method.
        return \iflow\event\Event::class;
    }
}