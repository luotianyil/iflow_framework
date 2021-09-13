<?php


namespace iflow\template\lib\document;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use iflow\template\lib\config;
use iflow\template\lib\document\Parser\ParserHtml;

/**
 * Class document
 * @mixin DOMDocument
 * @package iflow\template\lib\document
 */
class document
{
    protected DOMDocument $domDocument;

    public function __construct(DOMDocument|string $domDocument)
    {
        // 忽略检查 TAG 标签
        libxml_use_internal_errors(true);
        if (is_string($domDocument)) {
            $html = $domDocument;
            $domDocument = new DOMDocument();
            $domDocument -> loadHTML($html);
        }
        $this->domDocument = $domDocument;
    }

    /**
     * 获取Body
     * @return DOMNode|null
     */
    public function getBody(): DOMNode|null
    {
        $body = $this->domDocument -> getElementsByTagName('body');
        return $body -> item(0);
    }

    /**
     * 加载xml
     * @param string $xml
     * @return $this
     */
    public function loadXml(string $xml): static
    {
        $this->domDocument -> loadXML($xml);
        return $this;
    }

    /**
     * 加载html
     * @param string $html
     * @return $this
     */
    public function loadHTML(string $html): static
    {
        $this->domDocument -> loadHTML($html);
        return $this;
    }

    /**
     * 获取dom 子节点
     * @param DOMNode|null $node
     * @return DOMNodeList
     */
    public function getChildren(?DOMNode $node = null): DOMNodeList
    {
        return $node ? $node -> childNodes : $this->getBody() ?-> childNodes;
    }

    /**
     * 获取 HEADER
     * @return DOMNode|null
     */
    public function getHeader(): DOMNode|null
    {
        $header = $this->domDocument -> getElementsByTagName('header');
        return $header ?-> item(0);
    }

    public function getHtmlNode(): DOMNode|null
    {
        $html = $this->domDocument -> getElementsByTagName('html');
        return $html ?-> item(0);
    }

    /**
     * HTML TO PHPCode
     * @param array $config
     * @return string
     * @throws \Exception
     */
    public function htmlToPHPCode(array $config): string
    {
        return (new ParserHtml(new config($config), $this))
            -> traverseNodes();
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func([$this->domDocument, $name], ...$arguments);
    }
}
