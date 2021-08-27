<?php


namespace iflow\template\lib\document\Parser;


use iflow\template\lib\config;
use iflow\template\lib\document\document;

class ParserHtml
{

    protected PHPTag $PHPTag;
    protected ParserInstruction $ParserInstruction;

    public function __construct(
        protected config $config,
        protected document $document
    ) {
        $this->PHPTag = new PHPTag($this->config);
        $this->ParserInstruction = new ParserInstruction($this->config);
    }

    /**
     * 遍历节点获取HTML代码
     * @param \DOMNode|DOMNodeParser|null $htmlNode
     * @return string
     * @throws \Exception
     */
    public function traverseNodes(\DOMNode|DOMNodeParser $htmlNode = null): string
    {
        $nodes = $htmlNode ?: $this->document -> getHtmlNode();
        if (!$nodes) {
            return "";
        }

        $html = "";
        foreach ($nodes -> childNodes as $item) {
            if ($item instanceof \DOMText) {
                $html .= $item -> C14N();
                continue;
            }
            $dom = new DOMNodeParser($item);
            $parserPhp = $this->parserPHPTag($dom);
            if ($dom -> childNodes -> count() > 0) {
                if ($parserPhp) {
                    $html .= $parserPhp;
                } else {
                    $html .= $this->parserPHPInstruction(
                        $dom,
                        sprintf("<%s %s>%s</%s>", ...[
                            $dom -> nodeName,
                            // 过滤指令
                            $dom -> getAttributesToString(['i-if', 'i-for', 'i-elseif', 'i-else']),
                            $this->traverseNodes($dom),
                            $dom -> nodeName
                        ])
                    );
                }
            } else {
                $html .= $parserPhp ?: $this->parserPHPInstruction($dom, $dom -> innerHtml());
            }
        }

        return html_entity_decode($html);
    }

    /**
     * 解析 PHP 自定义DOM
     * @param DOMNodeParser $DOMNodeParser
     * @return string|null
     * @throws \Exception
     */
    public function parserPHPTag(DOMNodeParser $DOMNodeParser): string|null
    {
        return $this->PHPTag -> parserTag($DOMNodeParser, $this);
    }

    /**
     * 解析DOM 指令
     * @param DOMNodeParser $DOMNodeParser
     * @param string $html
     * @return string
     */
    public function parserPHPInstruction(DOMNodeParser $DOMNodeParser, string $html): string
    {
        return $this->ParserInstruction -> parserInstruction($DOMNodeParser, $html);
    }
}