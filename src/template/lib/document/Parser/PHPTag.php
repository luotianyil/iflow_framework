<?php


namespace iflow\template\lib\document\Parser;


use iflow\template\lib\config;
use iflow\template\lib\document\abstracts\tagAbstract;

class PHPTag
{
    // 解析 自定义 PHP TAG
    public function __construct(
        protected config $config,
    ) {}

    public function parserTag(DOMNodeParser $DOMNodeParser, ParserHtml $parserHtml): string|null
    {
        $tag = $this->config -> getTagByName(strtolower($DOMNodeParser -> nodeName));
        if ($tag) {
            $class = new $tag['class'];
            if ($class instanceof tagAbstract) {
                return $class
                        -> parser($DOMNodeParser, $this->config, $parserHtml)
                        -> toHtml();
            } else {
                throw new \Exception("tag instanceof tagAbstract failed");
            }
        }
        return null;
    }
}