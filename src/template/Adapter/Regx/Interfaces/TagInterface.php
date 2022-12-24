<?php

namespace iflow\template\Adapter\Regx\Interfaces;

interface TagInterface {

    public function parser(string $tag, string $html, array $args): TagInterface;

    public function toHtml(): string;

}