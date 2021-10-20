<?php


namespace iflow\session\lib;


interface Session
{

    public function initializer(array $config): static;

    public function get(string $name);
    public function set(string|null $name, array|string $default);
    public function delete(string $name);

    public function makeSessionID(): string;
}