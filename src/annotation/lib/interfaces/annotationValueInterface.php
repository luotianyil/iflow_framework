<?php


namespace iflow\annotation\lib\interfaces;


interface annotationValueInterface
{
    public function handle(\ReflectionProperty|\ReflectionParameter $ref, $object, array &$args = []);
}