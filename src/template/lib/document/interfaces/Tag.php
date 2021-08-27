<?php


namespace iflow\template\lib\document\interfaces;


use iflow\template\lib\config;
use iflow\template\lib\document\Parser\DOMNodeParser;
use iflow\template\lib\document\Parser\ParserHtml;

interface Tag
{
    public function parser(DOMNodeParser $node, config $config, ParserHtml $parserHtml): static;

    public function parserAttributes(): static;

    public function toHtml(): string;

    public function traverseNodesToHtml(): string;
}