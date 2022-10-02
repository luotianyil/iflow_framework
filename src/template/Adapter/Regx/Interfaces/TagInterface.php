<?php

namespace iflow\template\Adapter\Regx\Interfaces;

interface TagInterface {

    public function parser(string $tag, string $html): string;

    public function toHtml(): string;

}