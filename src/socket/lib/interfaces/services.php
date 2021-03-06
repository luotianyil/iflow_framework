<?php


namespace iflow\socket\lib\interfaces;


interface services
{

    public function start();

    public function createSocketServer(): static;

    public function wait(): static;

    public function close($socket = null);
}