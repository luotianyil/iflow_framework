<?php


namespace iflow\template;


use iflow\template\lib\Parser;

class Template extends Parser
{
    public function __construct(
        protected string $file = '',
        protected array $config = []
    ){
        $this->config(config('template'));
        if ($this->file !== '') {
            $view_suffix = $this->config['view_suffix'] === '' ? '' : ".{$this->config['view_suffix']}";
            $this->file = $this->config['view_root_path'] . $this->file . $view_suffix;
        }
    }
}