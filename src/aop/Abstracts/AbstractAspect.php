<?php


namespace iflow\aop\Abstracts;


abstract class AbstractAspect
{
    public array $classes = [];

    abstract public function process(\Closure $closure, array $args = []): mixed;
}