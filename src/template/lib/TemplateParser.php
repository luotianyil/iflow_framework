<?php


namespace iflow\template\lib;


interface TemplateParser
{
    public function config(array $config = []);

    public function exists();

    public function display(string $template, array $data = [], array $config = []);

    public function fetch(string $template, array $data = [], array $config = []);
}