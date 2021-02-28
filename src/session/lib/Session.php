<?php


namespace iflow\session\lib;


interface Session
{

    public function initializer(array $config): static;

    public function get(string $name);
    public function set(string|null $name, array $default);
    public function delete(string $name);
}