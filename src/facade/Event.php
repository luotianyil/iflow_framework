<?php


namespace iflow\facade;


use iflow\Faceted;

/**
 * @mixin \iflow\event\Event
 */
class Event extends Faceted
{

    protected static function getFaceClass(): string
    {
        // TODO: Implement getFaceClass() method.
        return \iflow\event\Event::class;
    }
}