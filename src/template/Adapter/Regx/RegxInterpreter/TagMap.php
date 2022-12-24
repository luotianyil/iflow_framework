<?php

namespace iflow\template\Adapter\Regx\RegxInterpreter;

use iflow\template\Adapter\Regx\Config;
use iflow\template\Adapter\Regx\RegxInterpreter\Traits\ReadRegxTagMap;

class TagMap {

    use ReadRegxTagMap;

    public function __construct(protected string $template = '', protected array $tagMap = [], protected ?Config $config = null) {
    }

}