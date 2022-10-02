<?php

namespace iflow\template\Adapter\Regx\implement\Abstracts;

use iflow\template\Adapter\Regx\Interfaces\TagInterface;

class TagAbstract implements TagInterface {

    protected string $source = '';
    protected string $html = '';
    protected string $tag = '';

    public function parser(string $tag, string $html): string {
        // TODO: Implement parser() method.
        $this->tag = $tag;
        $this->source = $html;
        return $html;
    }

    public function toHtml(): string {
        // TODO: Implement toHtml() method.
        return $this->html ?: $this->source;
    }
}