<?php


namespace iflow\template\lib;


use iflow\App;

class Parser implements TemplateParser
{

    private App $app;
    protected array $config = [];
    protected string $file;

    public function config(array $config = [])
    {
        // TODO: Implement config() method.
        $this->config = array_merge($this->config, $config);
    }

    public function exists()
    {
        // TODO: Implement exists() method.
    }

    public function display(string $template, array $data = [])
    {
        // TODO: Implement display() method.
        extract($data, EXTR_OVERWRITE);
    }

    public function fetch()
    {
        // TODO: Implement fetch() method.
    }

    public function templateParser()
    {

    }
}