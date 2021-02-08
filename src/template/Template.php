<?php


namespace iflow\template;


use iflow\template\lib\Parser;

class Template extends Parser
{
    public function __construct(
        protected string $file,
        protected array $config = [
            'store_path' => runtime_path() . 'template',
            'view_depr' => DIRECTORY_SEPARATOR,
            'view_suffix' => 'html',
        ]
    ){}
}