<?php


namespace iflow\template\lib\document\Tag;


use iflow\template\lib\config;
use iflow\template\lib\document\abstracts\tagAbstract;
use iflow\template\lib\document\Parser\DOMNodeParser;
use iflow\template\lib\document\Parser\ParserHtml;

class Db extends tagAbstract
{
    public string $tagName = "sql";

    // 查询语句
    protected string|null $sql = "";

    // 查询方法
    protected string|null $queryAction = "";
    // 查询模型
    protected string $queryModel = "";
    protected string|null $parameterName = "";

    protected array $hiddenAttributes = [
        'action', 'model', 'sql', 'parametername', 'props', 'tag'
    ];

    public function parser(DOMNodeParser $node, config $config, ParserHtml $parserHtml): static
    {
        parent::parser($node, $config, $parserHtml); // TODO: Change the autogenerated stub
        $this->queryAction = $this->node -> getAttributes('action') ?: 'query';
        $this->queryModel =  $this->node -> getAttributes('model') ?: '\think\facade\Db';
        $this->sql = $this->node -> getAttributes('sql');
        $this->parameterName = $this->node -> getAttributes('parameterName');
        return $this->parserAttributes();
    }

    public function parserAttributes(): static
    {
        // TODO: Implement parserAttributes() method.
        $this->html .= "<?php ";
        $this->html .= $this->parameterName ? "{$this -> parameterName} = " : "";
        $props = $this -> props ? "...[{$this -> props}]" : "";

        if ($this->sql) {
            $this->html .= "\\think\\facade\\Db::{$this -> queryAction}(\"{$this->sql}\", $props)";
        } else if ($this->queryModel) {
            $this->html .= "app('{$this -> queryModel}') -> {$this -> queryAction}($props)";
        }
        $this->html .= ";?>";
        $this->html .= $this->traverseNodesToHtml();
        return $this;
    }
}