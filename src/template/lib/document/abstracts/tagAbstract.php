<?php


namespace iflow\template\lib\document\abstracts;


use iflow\template\lib\config;
use iflow\template\lib\document\interfaces\Tag;
use iflow\template\lib\document\Parser\DOMNodeParser;
use iflow\template\lib\document\Parser\ParserHtml;
use iflow\template\lib\document\Parser\PHPTag;

abstract class tagAbstract implements Tag
{
    public string $tagName = "tag";
    public array $attributes = [];

    protected DOMNodeParser $node;
    protected config $config;
    protected PHPTag $PHPTag;
    protected ParserHtml $parserHtml;

    /**
     * 当前节点需要显示的TAG
     * @var string
     */
    protected string $defaultTagName = "";

    /**
     * 当前节点的 html代码
     * @var string
     */
    protected string $html = "";

    /**
     * 用户 指定的参数
     * @var string
     */
    protected string $props = "";

    // 敏感 Attributes TAG标签树形 可以指定
    protected array $hiddenAttributes = [];

    /**
     * 解析 DOM 节点数据
     * @param DOMNodeParser $node
     * @param config $config
     * @param ParserHtml $parserHtml
     * @return $this
     */
    public function parser(DOMNodeParser $node, config $config, ParserHtml $parserHtml): static
    {
        // TODO: Implement parser() method.
        $this->node = $node;
        $this->config = $config;
        $this->parserHtml = $parserHtml;
        $this->PHPTag = new PHPTag($this->config);


        $this->defaultTagName = $this->node -> getAttributes('tag') ?: "";
        $this->attributes = $this->node -> getAttributes() ?: [];

        // 获取props参数
        $this->props = $this->node -> getAttributes('props') ?: '';

        return $this;
    }

    /**
     * 解析 Attributes 参数
     * @return $this
     */
    public function parserAttributes(): static {
        return $this;
    }

    /**
     * 遍历子节点 返回子节点HTML
     * @return string
     * @throws \Exception
     */
    public function traverseNodesToHtml(): string {
        return $this->parserHtml -> traverseNodes(
            $this->node
        );
    }

    /**
     * 获取 HTML
     * @return string
     */
    public function toHtml(): string
    {
        // TODO: Implement toHtml() method.

        $this->hiddenAttributes = array_merge_recursive($this->hiddenAttributes, ['i-if', 'i-for', 'tag', 'i-elseif', 'i-else']);

        $attrs = "";
        foreach ($this->attributes as $attributeName => $attributeValue) {
            if (!in_array($attributeName, $this->hiddenAttributes)) {
                $attrs .= "${attributeName}=\"${attributeValue}\" ";
            }
        }

        if ($this->defaultTagName === "") {
            $html = sprintf("%s", $this->html);
        } else {
            $html = sprintf("<%s %s>%s</%s>", ...[
                $this->defaultTagName,
                $attrs,
                $this->html,
                $this->defaultTagName
            ]);
        }

        return $this->parserHtml -> parserPHPInstruction($this->node, $html);
    }
}