<?php


namespace iflow\template\lib\document\Parser;

use DOMNode;
use DOMText;

/**
 * @mixin DOMNode
 * @mixin DOMText
 * Class DOMNodeParser
 * @package iflow\template\lib\document\Parser
 */
class DOMNodeParser
{

    // 解析 DOMNode
    public function __construct(protected DOMNode|DOMText $DOMNode) {}

    /**
     * 获取 attributes
     * @param array|string $keys
     * @return array|string|null
     */
    public function getAttributes(
        array|string $keys = ""
    ): array|string|null
    {

        if (!$this->DOMNode -> attributes) return null;

        $attr = [];
        // 当key为空时获取全部
        if (!$keys) {
            foreach ($this->DOMNode -> attributes as $item) {
                $attr[$item -> nodeName] = $item -> nodeValue;
            }
            return $attr;
        }

        if (is_string($keys)) {
            return $this->DOMNode -> attributes ?-> getNamedItem(strtolower($keys)) ?-> textContent;
        }

        foreach ($keys as $key) {
            $attr[$key] = $this->DOMNode -> attributes -> getNamedItem($key) ?-> textContent;
        }
        return $attr;
    }


    /**
     * 获取 TAG属性
     * @param array $hidden 需要排除的 Tag属性
     * @return string
     */
    public function getAttributesToString(array $hidden = []): string
    {
        $attr = "";
        foreach ($this->DOMNode -> attributes as $item) {
            if (!in_array($item -> nodeName, $hidden)) {
                $attr .= "{$item -> nodeName}=\"{$item -> nodeValue}\" ";
            }
        }
        return trim($attr);
    }

    public function innerHtml(): string
    {
        return $this->DOMNode -> C14N();
    }

    /**
     * @param DOMNode $attribute
     * @return DOMNodeParser
     */
    public function setAttributes(DOMNode $attribute): DOMNodeParser
    {
        $this->DOMNode -> attributes -> setNamedItem($attribute);
        return $this;
    }

    /**
     * @return DOMNode
     */
    public function getDOMNode(): DOMNode
    {
        return $this->DOMNode;
    }

    public function getChildrenList(): \DOMNodeList
    {
        return $this->DOMNode -> childNodes;
    }

    /**
     * 获取下层DOM
     * @param ?DOMNode $node
     * @param bool $isText
     * @return DOMNodeParser|null
     */
    public function getNextNode(?DOMNode $node, bool $isText = false): ?DOMNodeParser
    {
        if ($node instanceof \DOMText && !$isText) {
            return $this->getNextNode($node -> nextSibling);
        }

        if (!$node) {
            return null;
        }
        return new DOMNodeParser($node);
    }

    public function __get(string $name)
    {
        // TODO: Implement __get() method.
        return $this->DOMNode -> {$name};
    }

    /**
     * 方法异常回调
     * @param string $name
     * @param array $arguments
     * @return false|mixed
     */
    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func([$this->DOMNode, $name], ...$arguments);
    }

}